import { Head, usePage } from '@inertiajs/react';

interface SeoProps {
    /** Page title — the app name suffix comes from the Inertia title template. */
    title: string;
    /** ~150 chars; falls back to the server-rendered default when omitted. */
    description?: string;
    /** Absolute URL of a 1200x630 share image. */
    image?: string;
    /** Canonical URL; defaults to the current URL. */
    canonical?: string;
}

/**
 * Per-page SEO head tags (guides/seo.md). Server-rendered defaults live in
 * resources/views/app.blade.php — this component overrides them client-side.
 * Crawler-critical pages may additionally need Inertia SSR (per-project
 * decision, ADR 0007).
 */
export default function Seo({
    title,
    description,
    image,
    canonical,
}: SeoProps) {
    const { url } = usePage();

    return (
        <Head title={title}>
            {description && <meta name="description" content={description} />}
            <meta property="og:title" content={title} />
            {description && (
                <meta property="og:description" content={description} />
            )}
            {image && <meta property="og:image" content={image} />}
            <link rel="canonical" href={canonical ?? url} />
        </Head>
    );
}
