import React, { FC } from 'react';
import { DatePicker } from '@wordpress/components';
import moment from 'moment';

interface DatePickerControlProps {
    help?: string;
    inputFormat?: string;
    label: string;
    name: string;
    onChange: (date: string) => void;
    onChangeFormat?: string;
    value: string;
}

const DatePickerControl: FC<DatePickerControlProps> = ({
    help,
    inputFormat = 'YYYY-MM-DD',
    label,
    name,
    onChange,
    onChangeFormat = 'YYYY-MM-DD',
    value,
}) => {
    return (
        <div className="ccwp-date-form-group" style={{ minHeight: '400px', marginBottom: '15px' }}>
            <label style={{ display: 'block', marginBottom: '5px' }}>
                <strong>{label}</strong>
            </label>
            <div>
                <DatePicker
                    currentDate={value ? moment(value).format('YYYY-MM-DDTHH:mm:ss') : undefined}
                    onChange={(newDate) => {
                        if (newDate) {
                            onChange(moment(newDate).format(onChangeFormat));
                        } else {
                            onChange('');
                        }
                    }}
                />
                <input type="hidden" name={name} value={value ? moment(value).format(inputFormat) : ''} />
            </div>
            {help && (
                <div className="ccwp-form-help-text" style={{ marginTop: '5px' }}>
                    <p>{help}</p>
                </div>
            )}
        </div>
    );
};

export default DatePickerControl;
