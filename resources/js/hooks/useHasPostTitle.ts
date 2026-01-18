import {useMemo} from 'react';
import {getPostTitle} from '../utils/post';
import useIsEditorReady from './useIsEditorReady';

const useHasPostTitle = () => {
    const isEditorReady = useIsEditorReady();
    return useMemo(
        () => isEditorReady ? !!getPostTitle() : false,
        [isEditorReady]
    );
};

export default useHasPostTitle;
