// ::: FUNCTIONS :::

/**
 * @param {number} ms
 */
function usleep( ms ) {
	return new Promise( resolve => setTimeout( resolve, ms ) )
}


/**
 * @return {number|string}
 */
function NumberWithin( value, min = 0, max = 1 ) {
	return Math.max( min, Math.min( max, value ) )
}