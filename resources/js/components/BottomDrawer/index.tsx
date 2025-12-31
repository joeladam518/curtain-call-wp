import {type FC, useEffect, useRef, useState} from 'react';
import {createPortal} from 'react-dom';
import DrawerContent from './DrawerContent';

const BottomDrawer: FC = () => {
    const [container, setContainer] = useState<HTMLElement | null>(null);
    const containerRef = useRef<HTMLDivElement | null>(null);

    useEffect(() => {
        // Find or create the drawer container
        let drawerContainer = document.getElementById('ccwp-bottom-drawer-container');

        if (!drawerContainer) {
            drawerContainer = document.createElement('div');
            drawerContainer.id = 'ccwp-bottom-drawer-container';
            drawerContainer.className = 'ccwp-bottom-drawer-wrapper';

            // Find the navigable region
            const navigableRegion = document.querySelector('.admin-ui-navigable-region.interface-interface-skeleton__content');

            if (navigableRegion) {
                // Find the metabox container
                const metaboxContainer = navigableRegion.querySelector('#postbox-container-0, .metabox-holder');

                if (metaboxContainer && metaboxContainer.nextSibling) {
                    // Insert after metabox container
                    navigableRegion.insertBefore(drawerContainer, metaboxContainer.nextSibling);
                } else if (metaboxContainer) {
                    // Append after metabox container
                    navigableRegion.appendChild(drawerContainer);
                } else {
                    // No metabox container found, just append to navigable region
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

        containerRef.current = drawerContainer as HTMLDivElement;
        setContainer(drawerContainer);
    }, []);

    if (!container) {
        return null;
    }

    return createPortal(<DrawerContent />, container);
};

BottomDrawer.displayName = 'BottomDrawer';

export default BottomDrawer;
