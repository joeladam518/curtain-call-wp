import {__} from '@wordpress/i18n';
import {FC} from 'react';
import {TEXT_DOMAIN} from '../utils/constants';

type MetaboxLabelRowProps = {
    type?: boolean
}

const MetaboxLabelRow: FC<MetaboxLabelRowProps> = ({
    type = false
}) => {
    return (
        <div
            className="ccwp-row label-row"
            style={{gridTemplateColumns: type ? '2fr 1fr 2fr 1fr 1fr' : '2fr 2fr 1fr 1fr'}}
        >
            <div className="ccwp-col name-col">{__('Name', TEXT_DOMAIN)}</div>
            {type && <div className="ccwp-col type-col">{__('Type', TEXT_DOMAIN)}</div>}
            <div className="ccwp-col role-col">{__('Role', TEXT_DOMAIN)}</div>
            <div className="ccwp-col billing-col">{__('Billing', TEXT_DOMAIN)}</div>
            <div className="ccwp-col action-col">&nbsp;</div>
        </div>
    )
}

MetaboxLabelRow.displayName = 'MetaboxLabelRow';

export default MetaboxLabelRow;
