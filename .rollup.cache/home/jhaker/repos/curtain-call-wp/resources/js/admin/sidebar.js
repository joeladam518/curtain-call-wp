import { jsx as _jsx, jsxs as _jsxs } from "@wordpress/element/jsx-runtime";
import React, { useEffect, useState } from 'react';
import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar } from '@wordpress/editor';
import { PanelBody, Button, SelectControl, TextControl, Spinner, Notice } from '@wordpress/components';
import { useSelect } from '@wordpress/data';
import apiFetch from '@wordpress/api-fetch';
const Sidebar = () => {
    const [loading, setLoading] = useState(false);
    const [relations, setRelations] = useState([]);
    const [error, setError] = useState(null);
    const [newType, setNewType] = useState('cast');
    const [newTargetId, setNewTargetId] = useState('');
    const [newRole, setNewRole] = useState('');
    const [newOrder, setNewOrder] = useState('');
    // Determine current post and post type from the editor
    const postId = useSelect((select) => select('core/editor').getCurrentPostId(), []);
    const postType = useSelect((select) => select('core/editor').getCurrentPostType(), []);
    const isProduction = postType === 'ccwp_production';
    const isCastCrew = postType === 'ccwp_cast_and_crew';
    // Load searchable options from core store
    const options = useSelect((select) => {
        const core = select('core');
        if (isProduction) {
            // Get cast & crew posts
            const records = core.getEntityRecords('postType', 'ccwp_cast_and_crew', { per_page: 50, _fields: ['id', 'title', 'meta'] }) || [];
            return records.map((r) => ({ label: r.title?.rendered || `#${r.id}`, value: String(r.id) }));
        }
        if (isCastCrew) {
            const records = core.getEntityRecords('postType', 'ccwp_production', { per_page: 50, _fields: ['id', 'title', 'meta'] }) || [];
            return records.map((r) => ({ label: r.title?.rendered || `#${r.id}`, value: String(r.id) }));
        }
        return [];
    }, [postType, isProduction, isCastCrew]);
    useEffect(() => {
        if (!postId)
            return;
        setLoading(true);
        setError(null);
        const query = {};
        if (isProduction)
            query.production_id = postId;
        if (isCastCrew)
            query.cast_and_crew_id = postId;
        apiFetch({ path: `/ccwp/v1/relations?` + new URLSearchParams(query).toString(), headers: { 'X-WP-Nonce': CCWP_SETTINGS?.nonce } })
            .then((rows) => setRelations(rows || []))
            .catch((e) => setError(e?.message || 'Failed to load relations'))
            .finally(() => setLoading(false));
    }, [postId, postType]);
    const attach = async () => {
        if (!newTargetId)
            return;
        const body = isProduction
            ? { production_id: postId, cast_and_crew_id: Number(newTargetId), type: newType, role: newRole, custom_order: newOrder ? Number(newOrder) : undefined }
            : { production_id: Number(newTargetId), cast_and_crew_id: postId, type: newType, role: newRole, custom_order: newOrder ? Number(newOrder) : undefined };
        setLoading(true);
        setError(null);
        try {
            await apiFetch({ path: `/ccwp/v1/relations`, method: 'POST', data: body, headers: { 'X-WP-Nonce': CCWP_SETTINGS?.nonce } });
            // reload
            const query = {};
            if (isProduction)
                query.production_id = postId;
            if (isCastCrew)
                query.cast_and_crew_id = postId;
            const rows = await apiFetch({ path: `/ccwp/v1/relations?` + new URLSearchParams(query).toString(), headers: { 'X-WP-Nonce': CCWP_SETTINGS?.nonce } });
            setRelations(rows || []);
            setNewTargetId('');
            setNewRole('');
            setNewOrder('');
        }
        catch (e) {
            setError(e?.message || 'Failed to attach');
        }
        finally {
            setLoading(false);
        }
    };
    const detach = async (r) => {
        setLoading(true);
        setError(null);
        try {
            const qs = new URLSearchParams({ production_id: String(r.production_id), cast_and_crew_id: String(r.cast_and_crew_id) }).toString();
            await apiFetch({ path: `/ccwp/v1/relations?${qs}`, method: 'DELETE', headers: { 'X-WP-Nonce': CCWP_SETTINGS?.nonce } });
            setRelations(relations.filter(x => !(x.production_id === r.production_id && x.cast_and_crew_id === r.cast_and_crew_id)));
        }
        catch (e) {
            setError(e?.message || 'Failed to detach');
        }
        finally {
            setLoading(false);
        }
    };
    const title = isProduction ? 'Production Cast & Crew' : 'Attached Productions';
    return (_jsx(PluginSidebar, { name: "ccwp-sidebar", title: title, icon: "admin-users", children: _jsxs(PanelBody, { title: title, initialOpen: true, children: [!!error && _jsx(Notice, { status: "error", isDismissible: false, children: error }), loading ? _jsx(Spinner, {}) : (_jsxs("div", { children: [_jsxs("div", { style: { marginBottom: 12 }, children: [_jsx(SelectControl, { label: "Type", value: newType, options: [{ label: 'Cast', value: 'cast' }, { label: 'Crew', value: 'crew' }], onChange: (v) => setNewType(v) }), _jsx(SelectControl, { label: isProduction ? 'Cast/Crew' : 'Production', value: newTargetId, options: [{ label: 'Select…', value: '' }, ...options], onChange: (v) => setNewTargetId(v), help: "Search and select an existing post to attach" }), _jsx(TextControl, { label: "Role", value: newRole, onChange: setNewRole }), _jsx(TextControl, { label: "Custom Order", type: "number", value: newOrder, onChange: setNewOrder }), _jsx(Button, { isPrimary: true, onClick: attach, disabled: !newTargetId, children: "Attach" })] }), _jsxs("div", { children: [relations.length === 0 && _jsx("p", { children: "No relations yet." }), relations.map((r) => (_jsxs("div", { style: { display: 'grid', gridTemplateColumns: 'auto 1fr auto', gap: 8, alignItems: 'center', marginBottom: 8 }, children: [_jsx("code", { children: r.type.toUpperCase() }), _jsxs("div", { children: [_jsxs("div", { children: ["Production: ", r.production_id, " | Cast/Crew: ", r.cast_and_crew_id] }), _jsxs("div", { style: { display: 'flex', gap: 8, marginTop: 6 }, children: [_jsx(TextControl, { label: "Role", value: r.role || '', onChange: (val) => setRelations(relations.map(x => x === r ? { ...x, role: val } : x)) }), _jsx(TextControl, { label: "Order", type: "number", value: typeof r.custom_order === 'number' ? String(r.custom_order) : '', onChange: (val) => setRelations(relations.map(x => x === r ? { ...x, custom_order: val === '' ? null : Number(val) } : x)) }), _jsx(Button, { onClick: async () => {
                                                                // Persist inline edits via attach upsert
                                                                await apiFetch({
                                                                    path: `/ccwp/v1/relations`,
                                                                    method: 'POST',
                                                                    data: {
                                                                        production_id: r.production_id,
                                                                        cast_and_crew_id: r.cast_and_crew_id,
                                                                        type: r.type,
                                                                        role: r.role,
                                                                        custom_order: r.custom_order,
                                                                    },
                                                                    headers: { 'X-WP-Nonce': CCWP_SETTINGS?.nonce },
                                                                });
                                                            }, children: "Save" })] })] }), _jsx(Button, { isSecondary: true, isDestructive: true, onClick: () => detach(r), children: "Remove" })] }, `${r.production_id}-${r.cast_and_crew_id}-${r.type}`)))] })] }))] }) }));
};
registerPlugin('ccwp-sidebar', { icon: undefined, render: Sidebar });
//# sourceMappingURL=sidebar.js.map