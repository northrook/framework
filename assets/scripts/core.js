import 'core/debug'
import 'core/functions'

logStopwatch( 'initial' )

const DOMLoadedEvent = () => logStopwatch( 'DOMLoadedEvent' )

window.addEventListener( 'DOMContentLoaded', DOMLoadedEvent )
