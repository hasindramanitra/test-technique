// assets/app.js

import { startStimulusApp } from '@symfony/stimulus-bridge';

// Démarrage Stimulus
const app = startStimulusApp(require.context(
    './controllers',
    true,
    /\.(j|t)sx?$/
));

console.log('App loaded!');
// plus besoin de registerControllers
