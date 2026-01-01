import {register} from '@wordpress/data';
import {registerPlugin} from '@wordpress/plugins';
import Sidebar from '../components/Sidebar';
import BottomDrawer from '../components/BottomDrawer';
import store from '../stores/relations-store';

// Register the attach sidebar panel
registerPlugin('ccwp-sidebar', {
    render: Sidebar,
});

// Register the bottom drawer
registerPlugin('ccwp-bottom-drawer', {
    render: BottomDrawer,
});

register(store);
