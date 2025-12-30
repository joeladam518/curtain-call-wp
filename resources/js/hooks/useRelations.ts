import {useEffect, useState, useCallback} from 'react';
import apiFetch from '@wordpress/api-fetch';
import {useSelect} from '@wordpress/data';
import {store as editorStore} from '@wordpress/editor';
import MemberType from '../enums/MemberType';
import {EditorSelectors} from '../types/stores';

export type Relation = {
    production_id: number;
    cast_and_crew_id: number;
    type: MemberType;
    role?: string;
    custom_order?: number | null;
};

export const useRelations = () => {
    const [loading, setLoading] = useState(false);
    const [relations, setRelations] = useState<Relation[]>([]);
    const [error, setError] = useState<string | null>(null);

    const postId = useSelect(select => (select(editorStore) as EditorSelectors).getCurrentPostId(), []);
    const postType = useSelect(select => (select(editorStore) as EditorSelectors).getCurrentPostType(), []);
    const isProduction = postType === 'ccwp_production';
    const isCastCrew = postType === 'ccwp_cast_and_crew';

    const fetchRelations = useCallback(async () => {
        if (!postId) {
            return;
        }

        setLoading(true);
        setError(null);

        const query: Record<string, string> = {};
        if (isProduction) {
            query.production_id = postId.toString();
        }
        if (isCastCrew) {
            query.cast_and_crew_id = postId.toString();
        }

        try {
            const value: unknown = await apiFetch({
                path: '/ccwp/v1/relations?' + new URLSearchParams(query).toString(),
                headers: {'X-WP-Nonce': window.CCWP_SETTINGS?.nonce ?? ''},
            });
            setRelations((value || []) as Relation[]);
        } catch (e: any) {
            setError(e?.message || 'Failed to load relations');
        } finally {
            setLoading(false);
        }
    }, [isCastCrew, isProduction, postId]);

    const attach = useCallback(async (
        targetId: number,
        type: MemberType,
        role?: string,
        customOrder?: number
    ) => {
        if (!postId) {
            throw new Error('No post ID');
        }

        const body: any = isProduction
            ? {
                production_id: postId,
                cast_and_crew_id: targetId,
                type,
                role,
                custom_order: customOrder,
            }
            : {
                production_id: targetId,
                cast_and_crew_id: postId,
                type,
                role,
                custom_order: customOrder,
            };

        setLoading(true);
        setError(null);

        try {
            await apiFetch({
                path: '/ccwp/v1/relations',
                method: 'POST',
                data: body,
                headers: {'X-WP-Nonce': window.CCWP_SETTINGS?.nonce ?? ''},
            });
            await fetchRelations();
        } catch (e: any) {
            setError(e?.message || 'Failed to attach');
            throw e;
        } finally {
            setLoading(false);
        }
    }, [postId, isProduction, fetchRelations]);

    const update = useCallback(async (relation: Relation) => {
        setLoading(true);
        setError(null);

        try {
            await apiFetch({
                path: '/ccwp/v1/relations',
                method: 'POST',
                data: {
                    production_id: relation.production_id,
                    cast_and_crew_id: relation.cast_and_crew_id,
                    type: relation.type,
                    role: relation.role,
                    custom_order: relation.custom_order,
                },
                headers: {'X-WP-Nonce': window.CCWP_SETTINGS?.nonce ?? ''},
            });
            await fetchRelations();
        } catch (e: any) {
            setError(e?.message || 'Failed to update');
            throw e;
        } finally {
            setLoading(false);
        }
    }, [fetchRelations]);

    const detach = useCallback(async (relation: Relation) => {
        setLoading(true);
        setError(null);

        try {
            const qs = new URLSearchParams({
                production_id: String(relation.production_id),
                cast_and_crew_id: String(relation.cast_and_crew_id),
            }).toString();
            await apiFetch({
                path: `/ccwp/v1/relations?${qs}`,
                method: 'DELETE',
                headers: {'X-WP-Nonce': window.CCWP_SETTINGS?.nonce ?? ''},
            });
            setRelations(
                relations.filter(
                    x => !(x.production_id === relation.production_id && x.cast_and_crew_id === relation.cast_and_crew_id)
                )
            );
        } catch (e: any) {
            setError(e?.message || 'Failed to detach');
            throw e;
        } finally {
            setLoading(false);
        }
    }, [relations]);

    useEffect(() => {
        fetchRelations();
    }, [fetchRelations]);

    return {
        relations,
        loading,
        error,
        attach,
        update,
        detach,
        refetch: fetchRelations,
        postType,
        isProduction,
        isCastCrew,
    };
};
