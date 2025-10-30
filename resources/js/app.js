import './bootstrap'

// import Alpine from 'alpinejs'
// import Clipboard from '@ryangjchandler/alpine-clipboard'

import.meta.glob(['../images/**'])

// Alpine.plugin(
//     Clipboard.configure({
//         onCopy: () => {
//             console.log('Copied!')
//         },
//     })
// )
// window.Alpine = Alpine

// Alpine.start()

// Add Livewire error handler for morph errors
document.addEventListener('DOMContentLoaded', () => {
    // Hook into Livewire's morph errors
    if (window.Livewire) {
        // Suppress "Could not find Livewire component in DOM tree" errors
        // These occur during race conditions when components are updated simultaneously
        const originalConsoleError = console.error
        console.error = function (...args) {
            const message = args[0]?.toString() || ''

            // Filter out specific Livewire morph errors that are non-critical
            if (
                message.includes(
                    'Could not find Livewire component in DOM tree',
                )
            ) {
                console.warn(
                    'Livewire component sync issue (non-critical):',
                    ...args,
                )
                return // Don't throw the error
            }

            // Pass through all other errors
            originalConsoleError.apply(console, args)
        }

        // Add Livewire hook to handle component initialization errors gracefully
        Livewire.hook('morph.updating', ({ component }) => {
            // Ensure component is still in DOM before morphing
            if (!component || !document.body.contains(component.el)) {
                console.warn('Component no longer in DOM, skipping morph')
                return false // Skip this morph
            }
        })
    }
})
