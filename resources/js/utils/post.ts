import {dispatch, select} from '@wordpress/data';

export function getPostTitle(): string | undefined {
    return select('core/editor').getEditedPostAttribute('title') || undefined;
}

export function updatePostTitle(title: string | null | undefined): void {
    dispatch('core/editor').editPost({title: title || ''});
}
