import {useSelect} from '@wordpress/data';
import {store as editorStore} from '@wordpress/editor';
import {useMemo} from 'react';

const useIsEditorReady = () => {
    const isEditorReady = useSelect(
        select => select(editorStore)?.__unstableIsEditorReady(),
        []
    );
    const isCleanNewPost = useSelect(
        select => select(editorStore)?.isCleanNewPost(),
        []
    );
    const blockCount = useSelect(
        select => select(editorStore)?.getBlockCount(),
        []
    );

    return useMemo(
        () => isEditorReady || isCleanNewPost || blockCount > 0,
        [isEditorReady, isCleanNewPost, blockCount]
    );
};

export default useIsEditorReady;
