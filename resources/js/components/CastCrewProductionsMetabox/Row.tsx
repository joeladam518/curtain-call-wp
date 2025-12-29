
import {Button, TextControl} from '@wordpress/components';
import {FC} from 'react';
import MemberType from '../../enums/MemberType';
import {CastCrewProduction} from '../../types/metaboxes';

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

const Row: FC<RowProps> = ({
    castCrewId,
    production,
    onUpdate,
    onRemove,
}) => {
    const inputName = `ccwp_add_${production.type}_to_cast_crew`;

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
                style={{display: 'flex', alignItems: 'center', gap: '10px', marginBottom: '10px'}}
            >
                <div className="ccwp-col name-col" style={{flex: '1'}}>
                    <div className="ccwp-production-name"><strong>{production.fullName}</strong></div>
                </div>
                <div className="ccwp-col role-col" style={{flex: '1'}}>
                    <TextControl
                        __next40pxDefaultSize
                        __nextHasNoMarginBottom
                        value={production.role || ''}
                        onChange={val => onUpdate?.(production.type, production.ID, {role: val})}
                        name={`${inputName}[${production.ID}][role]`}
                        placeholder="role"
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
                        placeholder="order"
                    />
                </div>
                <div className="ccwp-col action-col">
                    <Button
                        isDestructive
                        onClick={() => onRemove?.(production.type, production.ID)}
                    >
                        Delete
                    </Button>
                </div>
            </div>
        </div>
    );
};

Row.displayName = 'CastCrewProductionsMetaboxRow';

export default Row;
