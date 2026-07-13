# Issue 6 — Shared Persistent File Storage
> **Status: DEFERRED** — Safe to defer until horizontal scaling is needed. Current single-host Docker setup is already correct.

---

## Why it's deferred (not broken)

The Docker named volume `app_storage` is shared across **all services on the same host**:
```yaml
# docker-compose.yml — all 4 services mount the same volume
app:     volumes: [app_storage:/var/www/html/storage]
reverb:  volumes: [app_storage:/var/www/html/storage]
queue:   volumes: [app_storage:/var/www/html/storage]
nginx:   volumes: [app_storage:/var/www/html/storage]
```
Files survive container restarts and are visible to all services — this is fine for a single-server deployment.

---

## When to act

Act on this issue **before** either of these happens:
- [ ] Scaling `app` to multiple replicas on **different physical hosts** (Swarm / Kubernetes)
- [ ] Moving to a cloud hosting provider where container storage is ephemeral

---

## Current state of the code

All upload calls explicitly use the `'public'` disk (local):
```php
// AssetController.php
$request->file('photo')->store('assets', 'public');
$file->store('documents', 'public');

// EmployeeController.php
$request->file('photo')->store('employee-photos', 'public');

// BuildingController.php
$file->store('building-photos', 'public');
```

`config/filesystems.php` already has the S3 driver stub — just needs env vars:
```php
's3' => [
    'driver'   => 's3',
    'key'      => env('AWS_ACCESS_KEY_ID'),
    'secret'   => env('AWS_SECRET_ACCESS_KEY'),
    'region'   => env('AWS_DEFAULT_REGION'),
    'bucket'   => env('AWS_BUCKET'),
    'endpoint' => env('AWS_ENDPOINT'),  // for MinIO self-hosted
    ...
]
```

---

## Implementation steps (when ready)

### Option A — S3 or MinIO (recommended for horizontal scale)

1. **Install the S3 Flysystem adapter:**
   ```bash
   composer require league/flysystem-aws-s3-v3
   ```

2. **Set env vars in `.env`** (and production secrets store):
   ```dotenv
   FILESYSTEM_DISK=s3
   AWS_ACCESS_KEY_ID=your_key
   AWS_SECRET_ACCESS_KEY=your_secret
   AWS_DEFAULT_REGION=ap-southeast-1
   AWS_BUCKET=deped-zc-assets
   # For self-hosted MinIO:
   AWS_ENDPOINT=http://minio:9000
   AWS_USE_PATH_STYLE_ENDPOINT=true
   ```

3. **Add MinIO service to docker-compose.yml** (if self-hosting):
   ```yaml
   minio:
     image: minio/minio
     command: server /data --console-address ":9001"
     environment:
       MINIO_ROOT_USER: ${AWS_ACCESS_KEY_ID}
       MINIO_ROOT_PASSWORD: ${AWS_SECRET_ACCESS_KEY}
     volumes:
       - minio_data:/data
     ports:
       - "9000:9000"
       - "9001:9001"   # MinIO Console UI
     networks:
       - amu_network
   ```

4. **No controller changes needed** — all `store()` calls use the default disk, which will now be `s3`. Zero code changes required.

5. **Migrate existing uploads** (one-time script):
   ```bash
   # Write a custom Artisan command to re-upload storage/app/public/* to S3
   php artisan app:migrate-uploads-to-s3
   ```

6. **Verify:** Upload a file through the app, then confirm it appears in the S3/MinIO bucket — not on local disk.

---

### Option B — Shared NFS/network volume (simpler, single-datacenter)

Mount a shared NFS directory as the Docker volume instead of `local`:
```yaml
volumes:
  app_storage:
    driver: local
    driver_opts:
      type: nfs
      o: addr=<nfs-server-ip>,rw
      device: ":/path/to/shared/storage"
```
No code changes, no composer packages. Works for multi-node Docker Swarm on the same datacenter.

---

## Files to touch when implementing

| File | Change |
|---|---|
| `.env` | Set `FILESYSTEM_DISK=s3` and `AWS_*` vars |
| `docker-compose.yml` | Add MinIO service (Option A) or NFS volume (Option B) |
| `composer.json` | Add `league/flysystem-aws-s3-v3` (Option A only) |
| `config/filesystems.php` | No change needed — S3 stub already present |
| Controllers | **No change** — all use default disk |
