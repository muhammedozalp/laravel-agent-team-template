import { usePage } from '@inertiajs/react';

/**
 * i18n helpers (guides/i18n.md). Single source of truth is Laravel's lang/
 * directory — translations arrive as a shared Inertia prop, so there is no
 * client-side loading, no extra dependency, and backend validation messages
 * use the same files.
 */

interface I18nProps {
    locale: string;
    translations: Record<string, string>;
    [key: string]: unknown;
}

function replaceParams(
    text: string,
    params: Record<string, string | number> = {},
): string {
    return Object.entries(params).reduce(
        (result, [key, value]) =>
            result
                .replaceAll(
                    `:${key.toUpperCase()}`,
                    String(value).toUpperCase(),
                )
                .replaceAll(`:${key}`, String(value)),
        text,
    );
}

/**
 * Translate a key with :param interpolation — Laravel semantics.
 * Missing keys fall back to the key itself (matching Laravel's __()).
 */
export function useTranslations() {
    const { locale, translations } = usePage<I18nProps>().props;

    const t = (
        key: string,
        params: Record<string, string | number> = {},
    ): string => replaceParams(translations[key] ?? key, params);

    /**
     * Laravel trans_choice() semantics: supports `{0} none|[1,19] some|[20,*] many`
     * segments; plain `singular|plural` falls back to Intl.PluralRules so
     * many-form languages (e.g. Arabic) pluralize correctly.
     */
    const tChoice = (
        key: string,
        count: number,
        params: Record<string, string | number> = {},
    ): string => {
        const line = translations[key] ?? key;
        const segments = line.split('|');

        for (const segment of segments) {
            const exact = segment.match(/^\{(\d+)\}\s?(.*)/);
            if (exact && Number(exact[1]) === count) {
                return replaceParams(exact[2], { ...params, count });
            }
            const range = segment.match(/^\[(\d+),(\d+|\*)\]\s?(.*)/);
            if (
                range &&
                count >= Number(range[1]) &&
                (range[2] === '*' || count <= Number(range[2]))
            ) {
                return replaceParams(range[3], { ...params, count });
            }
        }

        const plain = segments.filter((s) => !/^[{[]/.test(s));
        if (plain.length > 1) {
            const rule = new Intl.PluralRules(locale).select(count);
            const index = rule === 'one' ? 0 : Math.min(plain.length - 1, 1);
            return replaceParams(plain[index], { ...params, count });
        }

        return replaceParams(plain[0] ?? line, { ...params, count });
    };

    return { t, tChoice, locale };
}
