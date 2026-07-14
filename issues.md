# Production Issue: Tailwind Play CDN Usage

The application currently loads Tailwind CSS via the runtime Play CDN script:

```html
<script src="https://cdn.tailwindcss.com"></script>
```

Using the Play CDN in production introduces several critical performance and user experience problems.

## Why This Is a Problem

### 1. High Performance Overhead (Runtime Compilation)
- **What happens**: Instead of using static, pre-compiled CSS rules, the browser must download the Tailwind compiler engine (written in JavaScript), parse the DOM, and dynamically generate CSS styles on the fly.
- **Impact**: This burns CPU cycles in the user's browser and delays page rendering, leading to lower Lighthouse performance scores and poor Core Web Vitals (specifically Largest Contentful Paint).

### 2. Large Asset Payload
- **What happens**: The runtime compiler is a large JavaScript bundle (~100kB+ compressed, ~400kB+ uncompressed).
- **Impact**: Users must download a massive compiler package just to render basic styles, which is extremely inefficient compared to a tiny, purged, static stylesheet.

### 3. Flash of Unstyled Content (FOUC)
- **What happens**: Because stylesheet generation is driven by JavaScript execution, HTML elements render before the script finished parsing class names and applying styles.
- **Impact**: Users see a brief, jarring view of raw, unstyled HTML before the page snaps into its intended layout.

### 4. No Caching of Compiled CSS
- **What happens**: The CSS is generated dynamically in memory every single time a page loads.
- **Impact**: The browser cannot cache a static CSS file, eliminating standard caching benefits across multiple pages or return visits.
