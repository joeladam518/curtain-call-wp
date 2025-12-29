import {
    format,
    formatISO,
    type FormatOptions,
    isDate,
    isValid,
    parse as parseFns,
    parseISO,
    type ParseOptions,
    toDate,
} from 'date-fns';

type DateToFormatInputValue = Date | string | null | undefined;
type DateToFormatOptions = {
    input?: string;
    output?: string;
    formatOptions?: FormatOptions;
    parseOptions?: ParseOptions;
};

export function dateToFormat(
    value: Date | null | undefined,
    format?: string
): string | undefined;
export function dateToFormat(
    value: DateToFormatInputValue,
    options?: DateToFormatOptions
): string | undefined;
export function dateToFormat(
    value: DateToFormatInputValue,
    options?: DateToFormatOptions | string
): string | undefined {
    if (!value) {
        return undefined;
    }

    const {
        input,
        output,
        parseOptions,
        formatOptions,
    } = ((typeof options === 'string' ? {output: options} : options) ?? {});

    const date = isDate(value)
        ? toDate(value)
        : input
            ? parse(value, input, parseOptions)
            : parseISO(value, parseOptions);

    if (!isValid(date)) {
        return undefined;
    }

    if (!output || output === 'iso') {
        return formatISO(date, formatOptions);
    }

    return format(date, output, formatOptions);
}

export function parse(dateStr: string, format: string, options?: ParseOptions): Date {
    const now = new Date();
    return parseFns(dateStr, format, new Date(now.getFullYear(), now.getMonth(), now.getDate(), 0, 0, 0, 0), options);
}
