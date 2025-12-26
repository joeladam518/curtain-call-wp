import {format, formatISO, type FormatOptions, isDate, isValid, parse, toDate} from 'date-fns';

type DateToFormatInputValue = Date | string | null | undefined;
type DateToFormatOptions = {
    input?: string,
    output?: string,
    formatOptions?: FormatOptions
}

export function dateToFormat(value: Date | null | undefined, format?: string): string | undefined
export function dateToFormat(value: DateToFormatInputValue, options?: DateToFormatOptions): string | undefined
export function dateToFormat(value: DateToFormatInputValue, options?: DateToFormatOptions | string): string | undefined {
    if (!value) {
        return undefined;
    }

    const {
        input = 'yyyy-MM-dd',
        output = 'yyyy-MM-dd',
        formatOptions,
    } = ((typeof options === 'string' ? {output: options} : options) ?? {});

    const date = isDate(value)
        ? toDate(value)
        : parse(value, input, new Date(0, 0, 0, 0, 0, 0, 0));

    if (!isValid(date)) {
        return undefined;
    }

    if (output === 'iso') {
        return formatISO(date, formatOptions);
    }

    return format(date, output, formatOptions);
}
