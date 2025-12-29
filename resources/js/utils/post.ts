import {dispatch, select} from '@wordpress/data';

export function getPostTitle(): string | undefined {
    const {getEditedPostAttribute} = select('core/editor');
    return getEditedPostAttribute('title') as string | undefined;
}

export function updatePostTitle(title: string | null | undefined): void {
    const {editPost} = dispatch('core/editor');
    editPost({title: title || ''});
}
