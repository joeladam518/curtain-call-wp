import {type FC, useLayoutEffect, useState} from 'react';
import {createPortal} from 'react-dom';
import DrawerContent from './DrawerContent';

const BottomDrawer: FC = () => {
    const [container, setContainer] = useState<HTMLElement | null>(null);

    useLayoutEffect(() => {
        // Find or create the drawer container
        let drawerContainer = document.getElementById('ccwp-bottom-drawer-container');

        if (!drawerContainer) {
            drawerContainer = document.createElement('div');
            drawerContainer.id = 'ccwp-bottom-drawer-container';
            drawerContainer.className = 'ccwp-bottom-drawer-wrapper';

            // Find the navigable region
            const navigableRegion = document.querySelector(
                '.admin-ui-navigable-region.interface-interface-skeleton__content'
            );

            if (navigableRegion) {
                // Insert the drawer container after the metaboxes if they are there (which they should be)
                const metaboxContainer = navigableRegion.querySelector('#postbox-container-0, .metabox-holder');
                if (metaboxContainer && metaboxContainer.nextSibling) {
                    navigableRegion.insertBefore(drawerContainer, metaboxContainer.nextSibling);
                } else {
                    navigableRegion.appendChild(drawerContainer);
                }
            } else {
                // Fallback: try to find alternate locations
                const fallbackRegion =
                    document.querySelector('.interface-interface-skeleton__content') ||
                    document.querySelector('.edit-post-layout__content') ||
                    document.body;
                fallbackRegion?.appendChild(drawerContainer);
            }
        }

        // eslint-disable-next-line react-hooks/set-state-in-effect
        setContainer(drawerContainer as HTMLDivElement);
    }, []);

    if (!container) {
        return null;
    }

    return createPortal(<DrawerContent />, container);
};

BottomDrawer.displayName = 'BottomDrawer';

export default BottomDrawer;
