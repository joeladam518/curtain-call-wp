import {registerPlugin} from '@wordpress/plugins';
import Sidebar from '../components/Sidebar';
import Index from '../components/BottomDrawer';
import TheatreCurtains from '../icons/TheatreCurtains';
// Import the store to ensure it's registered
import '../stores/relations-store';

// Register the attach sidebar
registerPlugin('ccwp-sidebar', {
    icon: TheatreCurtains,
    render: Sidebar
});

// Register the bottom drawer
registerPlugin('ccwp-bottom-drawer', {
    icon: TheatreCurtains,
    render: Index
});
