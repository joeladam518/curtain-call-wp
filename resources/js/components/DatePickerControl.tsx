import React, {FC, useState, useRef, useEffect, useCallback, useMemo} from 'react';
import {DatePicker, Popover} from '@wordpress/components';
import {format, formatISO, parse, isValid} from 'date-fns';
import {useMaskito} from '@maskito/react';
import {maskitoDateOptionsGenerator} from '@maskito/kit';
import type {MaskitoDateMode} from '@maskito/kit';

interface DatePickerControlProps {
    help?: string;
    inputFormat?: string;
    label: string;
    mask?: MaskitoDateMode;
    name: string;
    onChange: (date: string) => void;
    onChangeFormat?: string;
    value: string;
}

const DatePickerControl: FC<DatePickerControlProps> = ({
    help,
    inputFormat = 'yyyy-MM-dd',
    label,
    mask = ('mm/dd/yyyy' as MaskitoDateMode),
    name,
    onChange,
    onChangeFormat = 'yyyy-MM-dd',
    value,
}) => {
    const [isVisible, setIsVisible] = useState(false);
    const anchorRef = useRef<HTMLDivElement>(null!);
    const [anchorRect, setAnchorRect] = useState<DOMRect>({height: 0, width: 0, x: 0, y: 0} as DOMRect);

    // Sync input value with prop value
    const getDisplayValue = useCallback((dateValue: string) => {
        if (!dateValue) {
            return '';
        }
        try {
            const date = new Date(dateValue);
            return isValid(date) ? format(date, inputFormat) : '';
        } catch {
            return '';
        }
    }, [inputFormat]);

    const [inputValue, setInputValue] = useState(getDisplayValue(value));

    useEffect(() => {
        setInputValue(getDisplayValue(value));
    }, [value, getDisplayValue]);

    useEffect(() => {
        if (anchorRef.current) {
            setAnchorRect(anchorRef.current.getBoundingClientRect());
        }
    }, [anchorRef]);

    const handleTextChange = (e: React.ChangeEvent<HTMLInputElement>) => {
        const newValue = e.target.value;
        setInputValue(newValue);

        const parsedDate = parse(newValue, inputFormat, new Date());
        if (isValid(parsedDate)) {
            onChange(format(parsedDate, onChangeFormat));
        }
    };

    const handleDatePickerChange = (newDate: string | null) => {
        if (newDate) {
            const date = new Date(newDate);
            onChange(format(date, onChangeFormat));
            setInputValue(format(date, inputFormat));
        } else {
            onChange('');
            setInputValue('');
        }
    };

    const showPicker = () => setIsVisible(true);

    const maskitoOptions = useMemo(
        () => maskitoDateOptionsGenerator({mode: mask, separator: '/'}),
        [mask]
    );

    const maskitoRef = useMaskito({options: maskitoOptions});

    return (
        <div className="ccwp-date-form-group" style={{marginBottom: '15px'}}>
            <div ref={anchorRef} className="components-base-control">
                <div className="components-base-control__field">
                    {label && (
                        <label className="components-base-control__label">
                            {label}
                        </label>
                    )}
                    <input
                        ref={maskitoRef as any}
                        className="components-text-control__input"
                        value={inputValue}
                        onFocus={showPicker}
                        onInput={handleTextChange}
                        placeholder={inputFormat.toLowerCase()}
                        autoComplete="off"
                    />
                </div>
                {help && (
                    <p className="components-base-control__help">
                        {help}
                    </p>
                )}
            </div>
            {isVisible && (
                <Popover
                    anchorRect={anchorRect as any}
                    onClose={(() => setIsVisible(false)) as any}
                    placement="bottom-start"
                    focusOnMount={false as any}
                >
                    <div style={{padding: '10px'}}>
                        <DatePicker
                            currentDate={value ? formatISO(value) : undefined}
                            onChange={handleDatePickerChange}
                        />
                    </div>
                </Popover>
            )}
            <input
                type="hidden"
                name={name}
                value={value ? format(value, inputFormat) : ''}
            />
        </div>
    );
};

export default DatePickerControl;
