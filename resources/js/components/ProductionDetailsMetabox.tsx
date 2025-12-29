import {FC, useState} from 'react';
import {TextControl} from '@wordpress/components';
import useHasPostTitle from '../hooks/useHasPostTitle';
import {dateToFormat} from '../utils/dates';
import {updatePostTitle} from '../utils/post';
import DatePickerControl from './DatePickerControl';
import {ProductionDetails} from '../types/metaboxes';
import {format} from 'date-fns';

const getYear = (start: string | null | undefined, end: string | null | undefined): string | undefined => {
    const date = start || end || undefined;
    return date ? (format(date, 'yyyy') || undefined) : undefined;
};

const formatYear = (year: string | undefined): string => {
    return year ? `- ${year}` : '';
};

export type ProductionDetailsMetaboxProps = {
    initialDetails?: ProductionDetails | undefined;
};

const ProductionDetailsMetabox: FC<ProductionDetailsMetaboxProps> = ({initialDetails}) => {
    const hasPostTitle = useHasPostTitle();
    const [state, setState] = useState({
        name: initialDetails?.name || '',
        startDate: dateToFormat(initialDetails?.date_start, {input: 'yyyy-MM-dd', output: 'MM/dd/yyyy'}) || '',
        endDate: dateToFormat(initialDetails?.date_end, {input: 'yyyy-MM-dd', output: 'MM/dd/yyyy'}) || '',
        showTimes: initialDetails?.show_times || '',
        ticketUrl: initialDetails?.ticket_url || '',
        venue: initialDetails?.venue || '',
    });

    const setName = (value: string) => {
        setState(current => ({...current, name: value}));
        if (!hasPostTitle) {
            const year = formatYear(getYear(state.startDate, state.endDate));
            updatePostTitle(`${value || ''} ${year}`.trim());
        }
    };
    const setStartDate = (value: string) => {
        setState(current => ({...current, startDate: value}));
        if (!hasPostTitle) {
            const year = formatYear(getYear(value, state.endDate));
            updatePostTitle(`${state.name || ''} ${year}`.trim());
        }
    };
    const setEndDate = (value: string) => {
        setState(current => ({...current, endDate: value}));
        if (!hasPostTitle) {
            const year = formatYear(getYear(state.startDate, value));
            updatePostTitle(`${state.name || ''} ${year}`.trim());
        }
    };
    const setShowTimes = (value: string) => setState(current => ({...current, showTimes: value}));
    const setTicketUrl = (value: string) => setState(current => ({...current, ticketUrl: value}));
    const setVenue = (value: string) => setState(current => ({...current, venue: value}));

    return (
        <div className="ccwp-react-metabox">
            <TextControl
                __next40pxDefaultSize
                __nextHasNoMarginBottom
                label="Production Name"
                value={state.name}
                onChange={setName}
                name="ccwp_production_name"
                help="*Required. The production's name could be different than the post's title."
            />
            <div
                style={{
                    display: 'flex',
                    flexFlow: 'row nowrap',
                    justifyContent: 'flex-start',
                    alignItems: 'flex-start',
                    gap: '100px',
                }}
            >
                <DatePickerControl
                    label="Production Dates - Openening*"
                    name="ccwp_date_start"
                    onChange={setStartDate}
                    value={state.startDate}
                />
                <DatePickerControl
                    label="Production Dates - Closing*"
                    name="ccwp_date_end"
                    onChange={setEndDate}
                    value={state.endDate}
                />
            </div>
            <TextControl
                __next40pxDefaultSize
                __nextHasNoMarginBottom
                label="Show Times"
                value={state.showTimes}
                onChange={setShowTimes}
                name="ccwp_show_times"
                help="Example: Thurs-Fri 7:30pm - Sat & Sun 3:30pm & 8pm"
            />
            <TextControl
                __next40pxDefaultSize
                __nextHasNoMarginBottom
                label="URL for Online Ticket Sales"
                value={state.ticketUrl}
                onChange={setTicketUrl}
                name="ccwp_ticket_url"
                help='The "Default Tickets Url" will be used if no value is provided here.'
            />
            <TextControl
                __next40pxDefaultSize
                __nextHasNoMarginBottom
                label="Venue"
                value={state.venue}
                onChange={setVenue}
                name="ccwp_venue"
                help="Where the show was performed."
            />
        </div>
    );
};

ProductionDetailsMetabox.displayName = 'ProductionDetailsMetabox';

export default ProductionDetailsMetabox;
