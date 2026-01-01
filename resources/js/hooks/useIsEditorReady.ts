import {useSelect} from '@wordpress/data';
import {store as editorStore} from '@wordpress/editor';
import {useMemo} from 'react';

const useIsEditorReady = () => {
    const isEditorReady = useSelect(
        select => select(editorStore)?.__unstableIsEditorReady() as boolean,
        []
    );
    const isCleanNewPost = useSelect(
        select => select(editorStore)?.isCleanNewPost() as boolean,
        []
    );
    return useMemo(
        () => isEditorReady || isCleanNewPost,
        [isEditorReady, isCleanNewPost]
    );
};

export default useIsEditorReady;
