import React, {FC} from 'react';
import {CastCrewProduction} from '../../types/metaboxes';

export type CastCrewProductionMetaboxProps = {
    castCrewId: number | null,
    options: ({label: string, value: string})[];
    productions: CastCrewProduction[]
}

const CastCrewProductionMetabox: FC<CastCrewProductionMetaboxProps> = () => {
    return (
        <div className="ccwp-react-metabox"></div>
    )
}

CastCrewProductionMetabox.displayName = 'CastCrewProductionMetabox';

export default CastCrewProductionMetabox;
