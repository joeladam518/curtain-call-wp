import {createReduxStore, register} from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
import CastCrewData from '../data/CastCrewData';
import ProductionData from '../data/ProductionData';
import MemberType from '../enums/MemberType';

export type AttachData = {
    productionId: string | number;
    castcrewId: string | number;
    type: MemberType;
    role?: string;
    customOrder?: number;
};

export type DetachData = {
    productionId: string | number;
    castcrewId: string | number;
};

export type RelationsState = {
    relations: ((CastCrewData[]) | (ProductionData[]));
    loading: boolean;
    error: string | null;
};

const DEFAULT_STATE: RelationsState = {
    relations: [],
    loading: false,
    error: null,
};

// Actions
const actions = {
    setRelations(relations: ((CastCrewData[]) | (ProductionData[]))) {
        return {
            type: 'SET_RELATIONS' as const,
            payload: relations,
        };
    },

    setLoading(loading: boolean) {
        return {
            type: 'SET_LOADING' as const,
            payload: loading,
        };
    },

    setError(error: string | null) {
        return {
            type: 'SET_ERROR' as const,
            payload: error,
        };
    },

    fetchCastCrew: (productionId: string | number, type?: MemberType) => async ({dispatch}: {dispatch: any}) => {
        dispatch(actions.setLoading(true));
        dispatch(actions.setError(null));

        if (!productionId) {
            dispatch(actions.setLoading(false));
            return;
        }

        const query: Record<string, string> = {production_id: productionId.toString()};

        if (type) {
            query.type = type;
        }

        try {
            const value: unknown = await apiFetch({
                path: '/ccwp/v1/relations/cast-crew?' + new URLSearchParams(query).toString(),
                headers: {'X-WP-Nonce': window.CCWP_SETTINGS?.nonce ?? ''},
            });
            const records = (value || []) as Record<string, unknown>[];
            const castcrew = records.map(record => CastCrewData.fromRecord(record));
            dispatch(actions.setRelations(castcrew));
        } catch (e: any) {
            dispatch(actions.setError(e?.message || 'Failed to load pridutions'));
            throw e;
        } finally {
            dispatch(actions.setLoading(false));
        }
    },

    fetchProductions: (castcrewId: string | number) => async ({dispatch}: {dispatch: any}) => {
        dispatch(actions.setLoading(true));
        dispatch(actions.setError(null));

        if (!castcrewId) {
            dispatch(actions.setLoading(false));
            return;
        }

        const query: Record<string, string> = {cast_and_crew_id: castcrewId.toString()};

        try {
            const value: unknown = await apiFetch({
                path: '/ccwp/v1/relations/productions?' + new URLSearchParams(query).toString(),
                headers: {'X-WP-Nonce': window.CCWP_SETTINGS?.nonce ?? ''},
            });
            const records = (value || []) as Record<string, unknown>[];
            const productions = records.map(record => ProductionData.fromRecord(record));
            dispatch(actions.setRelations(productions));
        } catch (e: any) {
            dispatch(actions.setError(e?.message || 'Failed to load relations'));
            throw e;
        } finally {
            dispatch(actions.setLoading(false));
        }
    },

    attach: (props: AttachData) => async ({dispatch}: {dispatch: any}) => {
        dispatch(actions.setLoading(true));
        dispatch(actions.setError(null));

        try {
            await apiFetch({
                path: '/ccwp/v1/relations',
                method: 'POST',
                data: {
                    production_id: props.productionId,
                    cast_and_crew_id: props.castcrewId,
                    type: props.type,
                    role: props.role,
                    custom_order: props.customOrder,
                },
                headers: {'X-WP-Nonce': window.CCWP_SETTINGS?.nonce ?? ''},
            });
        } catch (e: any) {
            dispatch(actions.setError(e?.message || 'Failed to attach'));
            throw e;
        } finally {
            dispatch(actions.setLoading(false));
        }
    },

    attachCastCrew: (props: AttachData) => async ({dispatch}: {dispatch: any}) => {
        await dispatch(actions.attach(props));
        await dispatch(actions.fetchCastCrew(props.productionId));
    },

    attachProduction: (props: AttachData) => async ({dispatch}: {dispatch: any}) => {
        await dispatch(actions.attach(props));
        await dispatch(actions.fetchProductions(props.castcrewId));
    },

    detach: (props: DetachData) => async ({dispatch}: {dispatch: any}) => {
        dispatch(actions.setLoading(true));
        dispatch(actions.setError(null));

        const query: Record<string, string> = {
            production_id: props.productionId.toString(),
            cast_and_crew_id: props.castcrewId.toString(),
        };

        try {
            await apiFetch({
                path: `/ccwp/v1/relations?${new URLSearchParams(query).toString()}`,
                method: 'DELETE',
                headers: {'X-WP-Nonce': window.CCWP_SETTINGS?.nonce ?? ''},
            });
        } catch (e: any) {
            dispatch(actions.setError(e?.message || 'Failed to detach'));
            throw e;
        } finally {
            dispatch(actions.setLoading(false));
        }
    },

    detachCastCrew: (props: DetachData) => async ({dispatch, select}: {dispatch: any; select: any}) => {
        await dispatch(actions.detach(props));
        const relations = select.getRelations() as CastCrewData[];
        const filteredRelations = relations.filter(relation => relation.id !== props.castcrewId);
        dispatch(actions.setRelations(filteredRelations));
    },

    detachProduction: (props: DetachData) => async ({dispatch, select}: {dispatch: any; select: any}) => {
        await dispatch(actions.detach(props));
        const relations = select.getRelations() as ProductionData[];
        const filteredRelations = relations.filter(relation => relation.id !== props.productionId);
        dispatch(actions.setRelations(filteredRelations));
    },
};

type Action =
    | ReturnType<typeof actions.setRelations> |
    ReturnType<typeof actions.setLoading> |
    ReturnType<typeof actions.setError>;

const selectors = {
    getRelations(state: RelationsState): ((CastCrewData[]) | (ProductionData[])) {
        return state.relations || [];
    },

    isLoading(state: RelationsState): boolean {
        return state.loading;
    },

    getError(state: RelationsState): string | null {
        return state.error;
    },
};

// Reducer
function reducer(state: RelationsState = DEFAULT_STATE, action: Action): RelationsState {
    switch (action.type) {
        case 'SET_RELATIONS': {
            return {
                ...state,
                relations: action.payload as ((CastCrewData[]) | (ProductionData[])),
            };
        }
        case 'SET_LOADING': {
            return {
                ...state,
                loading: action.payload as boolean,
            };
        }
        case 'SET_ERROR': {
            return {
                ...state,
                error: action.payload as string | null,
            };
        }
        default:
            return state;
    }
}

// Type definitions for use in components
export type RelationsStoreActions = {
    setRelations: typeof actions.setRelations;
    setLoading: typeof actions.setLoading;
    setError: typeof actions.setError;
    fetchCastCrew: typeof actions.fetchCastCrew;
    fetchProductions: typeof actions.fetchProductions;
    attach: typeof actions.attach;
    attachCastCrew: typeof actions.attachCastCrew;
    attachProduction: typeof actions.attachProduction;
    detach: typeof actions.detach;
    detachCastCrew: typeof actions.detachCastCrew;
    detachProduction: typeof actions.detachProduction;
};

export type RelationsStoreSelectors = {
    getRelations: () => ((CastCrewData[]) | (ProductionData[]));
    isLoading: () => boolean;
    getError: () => string | null;
};

declare module '@wordpress/data' {
    function dispatch(key: 'ccwp/relations'): RelationsStoreActions;
    function select(key: 'ccwp/relations'): RelationsStoreSelectors;
    function useDispatch(key: 'ccwp/relations'): RelationsStoreActions;
    function useSelect(key: 'ccwp/relations'): RelationsStoreSelectors;
}

// Create and register the store
export const STORE_NAME = 'ccwp/relations';

export const relationsStore = createReduxStore(STORE_NAME, {
    reducer,
    actions,
    selectors,
});

register(relationsStore);
