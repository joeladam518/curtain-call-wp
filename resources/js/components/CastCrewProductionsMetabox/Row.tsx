import {Button, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import {FC} from 'react';
import MemberType from '../../enums/MemberType';
import {CastCrewProduction} from '../../types/metaboxes';
import {TEXT_DOMAIN} from '../../utils/constants';

type UpdateData = {
    role?: string;
    order?: number;
};

export type RowProps = {
    castCrewId: string | number | null;
    production: CastCrewProduction;
    onUpdate?: (type: MemberType, id: number | string, data: UpdateData) => void;
    onRemove?: (type: MemberType, id: number | string) => void;
};

//width: 100%;
//display: grid;
//grid-auto-flow: column;
//align-items: center;
//grid-template-columns: 2fr 2fr 1fr auto;
//gap: 12px;
//border-radius: 2px;
//padding: 12px;
//margin-bottom: 8px;

const Row: FC<RowProps> = ({
    castCrewId,
    production,
    onUpdate,
    onRemove,
}) => {
    const inputName = `ccwp_add_productions_to_${production.type}`;

    return (
        <div className="form-group ccwp-production-castcrew-form-group">
            <input
                type="hidden"
                name={`${inputName}[${production.ID}][production_id]`}
                value={production.ID}
            />
            <input
                type="hidden"
                name={`${inputName}[${production.ID}][cast_and_crew_id]`}
                value={castCrewId ?? ''}
            />
            <input
                type="hidden"
                name={`${inputName}[${production.ID}][type]`}
                value={production.type}
            />
            <div
                className="ccwp-row"
                style={{
                    maxWidth: '100%',
                    width: '100%',
                    display: 'grid',
                    gridAutoFlow: 'column',
                    alignItems: 'center',
                    gridTemplateColumns: '2fr 1fr 2fr 1fr 1fr',
                    gap: '12px',
                    marginBottom: '10px',
                }}
            >
                <div className="ccwp-col name-col" style={{flex: '1'}}>
                    <div className="ccwp-production-name">
                        <strong>{production.name}</strong>
                    </div>
                </div>
                <div className="ccwp-col type-col" style={{flex: '1'}}>
                    <div className="ccwp-member-type">
                        <strong>{production.type}</strong>
                    </div>
                </div>
                <div className="ccwp-col role-col" style={{flex: '1'}}>
                    <TextControl
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        value={production.role || ''}
                        onChange={val => onUpdate?.(production.type, production.ID, {role: val})}
                        name={`${inputName}[${production.ID}][role]`}
                        placeholder={__('role', TEXT_DOMAIN)}
                    />
                </div>
                <div className="ccwp-col billing-col" style={{flex: '0 0 100px'}}>
                    <TextControl
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        type="number"
                        value={production.order || 0}
                        onChange={val => onUpdate?.(production.type, production.ID, {order: parseInt(val, 10)})}
                        name={`${inputName}[${production.ID}][custom_order]`}
                        placeholder={__('order', TEXT_DOMAIN)}
                    />
                </div>
                <div className="ccwp-col action-col">
                    <Button
                        __next40pxDefaultSize
                        isDestructive
                        onClick={() => onRemove?.(production.type, production.ID)}
                    >
                        {__('Delete', TEXT_DOMAIN)}
                    </Button>
                </div>
            </div>
        </div>
    );
};

Row.displayName = 'CastCrewProductionsMetaboxRow';

export default Row;
