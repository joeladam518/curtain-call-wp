import {useEffect, useMemo, useState} from 'react';
import {getPostTitle} from '../utils/post';
import useIsEditorReady from './useIsEditorReady';

const useHasPostTitle = () => {
    const isEditorReady = useIsEditorReady();
    const [hasPostTitle, setHasPostTitle] = useState<boolean | null>(isEditorReady ? !!getPostTitle() : null);

    useEffect(() => {
        if (isEditorReady && hasPostTitle === null) {
            setHasPostTitle(!!getPostTitle());
        }
    }, [isEditorReady, hasPostTitle])

    return useMemo(() => !!hasPostTitle, [hasPostTitle]);
}

export default useHasPostTitle;
