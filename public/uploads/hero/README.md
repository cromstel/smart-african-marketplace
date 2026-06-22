# Hero Banner Images

This folder contains hero banner images for the website.

## Home Page Hero Image

**Current image:** `public/images/hero-banner.avif` (active)

**Alternate location:** `home-hero.jpg` (this directory)

**Required specifications:**
- **Dimensions:** 1920x800px (recommended) or 16:9 aspect ratio
- **Format:** JPG, JPEG, PNG, WEBP or AVIF
- **Max file size:** 2MB (compressed for web)
- **Content:** Modern indoor African mall with Afrofuturistic decor, African shoppers, young African business people between 25 and 45 in suits and African dress from Ghana, vibrant market stalls, lush indoor plants, natural daylight

**How to add/replace:**
1. Generate or download the hero image
2. Save it as `home-hero.jpg` in this directory OR replace `public/images/hero-banner.avif`
3. The image will automatically display on the homepage with a 50% opacity gradient overlay

**Priority order:**
1. Custom admin hero section image (if set in backend)
2. `public/images/hero-banner.avif` (currently active)
3. `public/uploads/hero/home-hero.jpg` (fallback)

**Image generation prompt:**
```
"Modern indoor African mall with Afrofuturistic decor, African shoppers, young African business people between 25 and 45 in business suits and traditional Ghanaian Kente cloth, vibrant market stalls selling African products, lush indoor plants, natural daylight streaming through large windows, photorealistic, wide angle, 16:9 aspect ratio, professional photography"
```

**Recommended AI tools:**
- DALL-E 3 (via ChatGPT Plus or OpenAI API)
- Midjourney (Discord-based)
- Leonardo.ai
- Stable Diffusion

**Stock photo sources:**
- Unsplash.com
- Pexels.com
- Shutterstock.com

## Notes

- The hero component automatically applies a 50% opacity gradient overlay (orange to red) over the background image
- The text remains fully visible with proper contrast
- The image will cover the entire hero section and scale responsively
- If no image is found, the component falls back to a solid gradient background
