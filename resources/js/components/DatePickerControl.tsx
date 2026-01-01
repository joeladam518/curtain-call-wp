import {type FC, useState, useRef, useMemo, memo, useEffect, type ChangeEvent, type KeyboardEvent} from 'react';
import {DatePicker, Popover, BaseControl} from '@wordpress/components';
import {format, parseISO, isValid} from 'date-fns';
import {useMaskito} from '@maskito/react';
import {maskitoDateOptionsGenerator, type MaskitoDateMode} from '@maskito/kit';
import {dateToFormat} from '../utils/dates';

type DatePickerControlState = {
    isVisible: boolean;
};

type DatePickerControlProps = {
    help?: string;
    hideLabelFromVision?: boolean;
    inputFormat?: string;
    label: string;
    mask?: MaskitoDateMode;
    name: string;
    onChange?: (date: string) => void;
    value: string;
    valueFormat?: string;
};

const DatePickerControl: FC<DatePickerControlProps> = memo(({
    help,
    hideLabelFromVision = false,
    inputFormat = 'yyyy-MM-dd',
    label,
    mask = ('mm/dd/yyyy' as MaskitoDateMode),
    name,
    onChange,
    value,
    valueFormat = 'MM/dd/yyyy',
}) => {
    const [state, setState] = useState<DatePickerControlState>({isVisible: false});
    const inputRef = useRef<HTMLInputElement | null>(null);
    const datePickerRef = useRef<HTMLDivElement | null>(null);
    const maskitoOptions = useMemo(
        () => maskitoDateOptionsGenerator({mode: mask, separator: '/'}),
        [mask]
    );
    const maskitoRef = useMaskito({options: maskitoOptions});

    const datePickerValue = useMemo<string | undefined>(
        () => dateToFormat(value, {input: valueFormat, output: 'iso'}),
        [value, valueFormat]
    );

    const hiddenInputValue = useMemo<string>(
        () => dateToFormat(value, {input: valueFormat, output: inputFormat}) || '',
        [value, valueFormat, inputFormat]
    );

    const handleTextChange = (event: ChangeEvent<HTMLInputElement>) => {
        const newValue = event.target.value;
        onChange?.(newValue || '');
    };

    const handleDatePickerChange = (newValue: string | null) => {
        if (!newValue) {
            return;
        }
        const newDate = parseISO(newValue);
        if (!isValid(newDate)) {
            return;
        }
        const formattedDate = format(newDate, valueFormat);
        onChange?.(formattedDate);
    };

    const handleKeyDown = (event: KeyboardEvent<HTMLInputElement>) => {
        if (event.key === 'Enter') {
            event.preventDefault();
            setState(current => ({...current, isVisible: false}));
            inputRef.current?.blur();
        }
    };

    useEffect(() => {
        const closePopover = () => {
            if (
                !!inputRef.current &&
                !!datePickerRef.current &&
                inputRef.current !== document.activeElement &&
                datePickerRef.current !== document.activeElement &&
                !datePickerRef.current.contains(document.activeElement as Node)
            ) {
                setState(current => ({...current, isVisible: false}));
            }
        };

        document.addEventListener('focusin', closePopover);

        return () => {
            document.removeEventListener('focusin', closePopover);
        };
    }, []);

    return (
        <div className="ccwp-date-picker-control">
            <BaseControl
                __nextHasNoMarginBottom={true}
                __associatedWPComponentName="DatePickerControl"
                help={help}
                hideLabelFromVision={hideLabelFromVision}
                label={label}
            >
                <input
                    ref={(inputElement) => {
                        maskitoRef(inputElement);
                        inputRef.current = inputElement;
                    }}
                    className="components-text-control__input is-next-40px-default-size"
                    onFocus={() => {
                        setState(current => ({...current, isVisible: true}));
                    }}
                    onKeyDown={handleKeyDown}
                    value={value}
                    onInput={handleTextChange}
                    placeholder={mask.toLowerCase()}
                    autoComplete="off"
                />
            </BaseControl>
            {state.isVisible && (
                <Popover
                    placement="bottom-start"
                    focusOnMount={false as any}
                >
                    <div ref={datePickerRef} className="ccwp-date-picker-container">
                        <DatePicker
                            currentDate={datePickerValue}
                            onChange={handleDatePickerChange}
                        />
                    </div>
                </Popover>
            )}
            <input
                type="hidden"
                name={name}
                value={hiddenInputValue}
            />
        </div>
    );
});

DatePickerControl.displayName = 'DatePickerControl';

export default DatePickerControl;
