import {dispatch, select} from '@wordpress/data';
import {store as editorStore} from '@wordpress/editor';

export function getPostTitle(): string | undefined {
    const {getEditedPostAttribute} = select(editorStore);
    return getEditedPostAttribute('title') as string | undefined;
}

export function updatePostTitle(title: string | null | undefined): void {
    const {editPost} = dispatch(editorStore);
    editPost({title: title || ''});
}
