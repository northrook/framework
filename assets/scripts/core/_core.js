logStopwatch( 'initial' )

const DOMLoadedEvent = () => logStopwatch( 'DOMLoadedEvent' )

window.addEventListener( 'DOMContentLoaded', DOMLoadedEvent )
