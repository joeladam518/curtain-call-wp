import {FC, useState} from 'react';
import {Button, ComboboxControl, SelectControl, TextControl} from '@wordpress/components';
import {__} from '@wordpress/i18n';
import MemberType from '../../enums/MemberType';
import {CastCrewProduction} from '../../types/metaboxes';
import {TEXT_DOMAIN} from '../../utils/constants';
import MetaboxLabelRow from '../MetaboxLabelRow';
import Row from './Row';

type UpdateData = {
    role?: string;
    order?: number;
};

export type MetaboxState = {
    cast: CastCrewProduction[];
    crew: CastCrewProduction[];
    selectedProductionId: string;
    selectedType: MemberType;
    role: string;
    order: number;
};

export type CastCrewProductionsMetaboxProps = {
    castCrewId: number | null;
    options: ({label: string; value: string})[];
    productions: CastCrewProduction[];
};

const CastCrewProductionsMetabox: FC<CastCrewProductionsMetaboxProps> = ({
    castCrewId,
    options = [],
    productions: initialProductions,
}) => {
    const [state, setMetaboxState] = useState<MetaboxState>({
        cast: initialProductions?.filter(p => p.type === MemberType.Cast) as CastCrewProduction[] ?? [],
        crew: initialProductions?.filter(p => p.type === MemberType.Crew) as CastCrewProduction[] ?? [],
        selectedProductionId: '0',
        selectedType: MemberType.Cast,
        role: '',
        order: 0,
    });

    const setCast = (cast: CastCrewProduction<MemberType.Cast>[]) => setMetaboxState(
        current => ({...current, cast})
    );
    const addCast = (cast: CastCrewProduction<MemberType.Cast>) => setMetaboxState((current) => {
        const castProductions = [...current.cast];
        castProductions.push(cast);
        return {...current, cast: castProductions};
    });
    const setCrew = (crew: CastCrewProduction<MemberType.Crew>[]) => setMetaboxState(
        current => ({...current, crew})
    );
    const addCrew = (crew: CastCrewProduction<MemberType.Crew>) => setMetaboxState((current) => {
        const crewProductions = [...current.crew];
        crewProductions.push(crew);
        return {...current, crew: crewProductions};
    });
    const setSelectedProductionId = (id: string) => setMetaboxState(current => ({
        ...current,
        selectedProductionId: id,
    }));
    const setSelectedType = (type: MemberType) => setMetaboxState(current => ({...current, selectedType: type}));
    const setRole = (role: string) => setMetaboxState(current => ({...current, role}));
    const setOrder = (order: number) => setMetaboxState(current => ({...current, order}));

    const addProduction = () => {
        const id = parseInt(state.selectedProductionId, 10);

        if (!id) {
            return;
        }

        const option = options.find(production => production.value === state.selectedProductionId);

        if (!option) {
            return;
        }

        const newProduction: CastCrewProduction = {
            ID: id,
            dateEnd: null,
            dateStart: null,
            name: option.label,
            order: state.order,
            role: state.role || null,
            type: state.selectedType,
        };

        if (state.selectedType === MemberType.Cast) {
            addCast(newProduction as CastCrewProduction<MemberType.Cast>);
        } else if (state.selectedType === MemberType.Crew) {
            addCrew(newProduction as CastCrewProduction<MemberType.Crew>);
        }

        // Reset form fields
        setMetaboxState(current => ({
            ...current,
            selectedProductionId: '0',
            role: '',
            order: 0,
        }));
    };

    const removeProduction = (type: MemberType, id: number | string) => {
        if (type === MemberType.Cast) {
            setCast(state.cast.filter(p => p.ID !== id) as CastCrewProduction<MemberType.Cast>[]);
        } else if (type === MemberType.Crew) {
            setCrew(state.crew.filter(p => p.ID !== id) as CastCrewProduction<MemberType.Crew>[]);
        }
    };

    const updateProduction = (type: MemberType, id: number | string, data: UpdateData) => {
        const updateFn = (productions: CastCrewProduction[]) => productions.map(p => p.ID === id ? {...p, ...data} : p);
        if (type === MemberType.Cast) {
            const newCast = updateFn(state.cast) as CastCrewProduction<MemberType.Cast>[];
            setCast(newCast);
        } else if (type === MemberType.Crew) {
            const newCrew = updateFn(state.crew) as CastCrewProduction<MemberType.Crew>[];
            setCrew(newCrew);
        }
    };

    return (
        <div className="ccwp-react-metabox">
            {/* Add Production Form */}
            <div
                className="ccwp-production-castcrew-select-wrap"
                style={{
                    display: 'grid',
                    gridAutoFlow: 'column',
                    alignItems: 'end',
                    gridTemplateColumns: '2fr 1fr 2fr 1fr 1fr',
                    gap: '10px',
                    marginBottom: '20px',
                }}
            >
                <ComboboxControl
                    __next40pxDefaultSize
                    __nextHasNoMarginBottom
                    label={__('Production', TEXT_DOMAIN)}
                    value={state.selectedProductionId}
                    options={[{label: __('Select Production', TEXT_DOMAIN), value: '0'}, ...options]}
                    onChange={value => setSelectedProductionId(value || '0')}
                />
                <SelectControl<MemberType>
                    __next40pxDefaultSize
                    __nextHasNoMarginBottom
                    label={__('Type', TEXT_DOMAIN)}
                    value={state.selectedType}
                    options={[
                        {label: __('Cast', TEXT_DOMAIN), value: MemberType.Cast},
                        {label: __('Crew', TEXT_DOMAIN), value: MemberType.Crew},
                    ]}
                    onChange={value => setSelectedType(value as MemberType)}
                />
                <TextControl
                    __next40pxDefaultSize
                    __nextHasNoMarginBottom
                    label={__('Role', TEXT_DOMAIN)}
                    value={state.role}
                    onChange={value => setRole(value)}
                    placeholder={__('e.g. Hamlet, Director', TEXT_DOMAIN)}
                />
                <TextControl
                    __next40pxDefaultSize
                    __nextHasNoMarginBottom
                    label={__('Order', TEXT_DOMAIN)}
                    type="number"
                    value={state.order}
                    onChange={value => setOrder(parseInt(value, 10) || 0)}
                    placeholder="0"
                />
                <Button
                    __next40pxDefaultSize
                    variant="secondary"
                    onClick={() => addProduction()}
                >
                    {__('Add Production', TEXT_DOMAIN)}
                </Button>
            </div>

            <div id="ccwp-cast-crew-cast-wrap">
                {(state.cast.length > 0 || state.crew.length > 0) && (
                    <MetaboxLabelRow type />
                )}
                {state.cast.map((production: CastCrewProduction, index: number) => (
                    <Row
                        key={`add_cast_row_${production.ID}_${index}`}
                        castCrewId={castCrewId}
                        production={production}
                        onUpdate={updateProduction}
                        onRemove={removeProduction}
                    />
                ))}
                {state.crew.map((production: CastCrewProduction, index: number) => (
                    <Row
                        key={`add_crew_row_${production.ID}_${index}`}
                        castCrewId={castCrewId}
                        production={production}
                        onUpdate={updateProduction}
                        onRemove={removeProduction}
                    />
                ))}
            </div>
        </div>
    );
};

CastCrewProductionsMetabox.displayName = 'CastCrewProductionsMetabox';

export default CastCrewProductionsMetabox;
