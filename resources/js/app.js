import './bootstrap';

import Alpine from 'alpinejs';
import Clipboard from "@ryangjchandler/alpine-clipboard"

import.meta.glob([
    '../images/**',
]);

Alpine.plugin(Clipboard.configure({
    onCopy: () => {
        console.log('Copied!')
    }
}))
window.Alpine = Alpine;

Alpine.start();
