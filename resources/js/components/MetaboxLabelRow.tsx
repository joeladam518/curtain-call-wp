import {FC} from 'react';

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
            <div className="ccwp-col name-col">Name</div>
            {type && <div className="ccwp-col type-col">Type</div>}
            <div className="ccwp-col role-col">Role</div>
            <div className="ccwp-col billing-col">Billing</div>
            <div className="ccwp-col action-col">&nbsp;</div>
        </div>
    )
}

MetaboxLabelRow.displayName = 'MetaboxLabelRow';

export default MetaboxLabelRow;
