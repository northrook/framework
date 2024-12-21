// ::: DEBUG :::

let lastTime = Date.now() // Initialize with the current time

function logStopwatch( description ) {
	const currentTime = Date.now()
	console.info( `${ description }: ${ currentTime - lastTime } offset` )
	lastTime = currentTime
}