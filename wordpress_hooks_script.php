
<?php 

// @note: Maybe have a custom thumbnail name where you can specify the size and quality, 
// @note:   - e.g. "thumbor_900_600_80" =>  wp_get_attachment_image_src( 1374, 'thumbor_900_600_80' );

define( 'PD_IS_LOCALHOST', false ); // Set to true if running on localhost for testing purposes
define( 'CRUNCH_SERVER_URL', 'http://127.0.0.1:8888' ); // Replace with your actual Thumbor server URL

function get_local_thumbor_image( $originalUrl, $width, $height, $quality = 80 ) {
	if ( defined( 'PD_IS_LOCALHOST' ) && PD_IS_LOCALHOST ) {
		// error_log( 'Thumbor: Running on localhost, skipping remote fetch.' );
		return null; // Skip processing on localhost.
	}

	if ( empty( $originalUrl ) ) {
		// error_log( 'Thumbor: original URL is empty.' );
		return null;
	}

	// Skip already cached Thumbor paths.
	if ( strpos( $originalUrl, '/wp-content/thumbor-cache/' ) !== false ) {
		return $originalUrl;
	}

	// Extract original filename.
	$parsedUrl     = parse_url( $originalUrl );
	$path          = $parsedUrl['path'] ?? '';
	$filename      = basename( $path );
	$filenameNoExt = preg_replace( '/\.[^.]+$/', '', $filename ); // Strip extension.

	// Build local cache path.
	$filenameSafe  = sanitize_file_name( $filenameNoExt );
	$localFilename = "{$filenameSafe}-{$width}x{$height}-q{$quality}.webp";
	$localRelPath  = "/wp-content/thumbor-cache/{$localFilename}";
	$fullLocalPath = $_SERVER['DOCUMENT_ROOT'] . $localRelPath;

	// If it already exists, just return the path.
	if ( file_exists( $fullLocalPath ) ) {
		return $localRelPath;
	}

	// On live/dev: generate via Thumbor.
	@mkdir( dirname( $fullLocalPath ), 0755, true );
	$thumborUrl = CRUNCH_SERVER_URL . "/unsafe/fit-in/{$width}x{$height}/filters:format(webp):quality({$quality})/" . urlencode( $originalUrl );

	$imageData = @file_get_contents( $thumborUrl );

	if ( $imageData && strpos( $imageData, '<!DOCTYPE html>' ) === false ) {
		file_put_contents( $fullLocalPath, $imageData );
		return $localRelPath;
	} else {
		error_log( "Thumbor fetch failed or invalid data: $thumborUrl" );
		return $originalUrl; // Fallback to full original.
	}
}
add_filter( 'image_downsize', 'custom_thumbor_image_downsize', 10, 3 );

function custom_thumbor_image_downsize( $out, $id, $size ) {
    if ( is_admin() || $size === 'full' ) {
        return false;
    }

    $original_url = wp_get_attachment_url( $id );
    if ( ! $original_url ) {
        return false;
    }

    // Named fallback sizes.
    $sizes = array(
        'thumbnail'                        => array( 150, 150 ),
        'medium'                           => array( 300, 300 ),
        'large'                            => array( 1024, 1024 ),
        'pebble_thumb_list_rooms_tablet'   => array( 900, 600 ),
    );

    $width   = null;
    $height  = null;
    $quality = null;

    // Support dynamic thumbor_900_600_80 style.
    if ( is_string( $size ) && preg_match( '/^thumbor_(\d+)_(\d+)_(\d+)$/', $size, $matches ) ) {
        $width   = (int) $matches[1];
        $height  = (int) $matches[2];
        $quality = (int) $matches[3];
    } elseif ( isset( $sizes[ $size ] ) ) {
        list( $width, $height ) = $sizes[ $size ];
        $quality = 80; // Default quality.
    } elseif ( is_array( $size ) && count( $size ) >= 2 ) {
        $width   = (int) $size[0];
        $height  = (int) $size[1];
        $quality = 80;
    }

    if ( ! $width || ! $height || ! $quality ) {
        return false;
    }

    $thumbor_image = get_local_thumbor_image( $original_url, $width, $height, $quality );
    if ( $thumbor_image ) {
        return array( $thumbor_image, $width, $height, true );
    }

    return false;
}

/*
function custom_thumbor_image_downsize( $out, $id, $size ) {
	// Bail early in admin or if $size is 'full'.
	if ( is_admin() || 'full' === $size ) {
		return false;
	}

	// Get original image URL.
	$originalUrl = wp_get_attachment_url( $id );
	if ( ! $originalUrl ) {
		return false;
	}

	// Define your custom sizes — OR parse from $size if it's an array.
	if ( is_array( $size ) ) {
		$width  = $size[0];
		$height = $size[1];
	} else {
		// Fallback mapping for named sizes.
        // @note: add dynamic thumbnail sizes, e.g. 'thumbor_900_600_80' to be appended to the list.
        $sizes = array(
            'pebble_thumb_list_rooms_tablet' => array( 900, 600 ),
        );

        // Support dynamic sizes like 'thumbor_900_600_80'
        if ( preg_match( '/^thumbor_(\d+)_(\d+)_(\d+)$/', $size, $matches ) ) {
            $width  = (int) $matches[1];
            $height = (int) $matches[2];
            $quality = (int) $matches[3];
            return array(
                get_local_thumbor_image( $originalUrl, $width, $height, $quality ),
                $width,
                $height,
                true,
            );
        }

		if ( ! isset( $sizes[ $size ] ) ) {
			return false;
		}

		list( $width, $height ) = $sizes[ $size ];
	}

	// Build your Thumbor image.
	$thumborImage = get_local_thumbor_image( $originalUrl, $width, $height );
	if ( $thumborImage ) {
		return array(
			$thumborImage, // src.
			$width,
			$height,
			true, // is_intermediate.
		);
	}

	return false; // Fallback to WP default behavior.
} */

// $thumborUrl = wp_get_attachment_url(1374);
// echo '<img src="' . esc_url($thumborUrl) . '" width="900" height="600">';


$test_src = wp_get_attachment_image_src( 1374, 'pebble_thumb_list_rooms_tablet' );

var_dump( $test_src ); // For debugging purposes, remove in production
echo '<img src="' . esc_url( $test_src[0] ) . '" alt="Test Image" width="' . esc_attr( $test_src[1] ) . '" height="' . esc_attr( $test_src[2] ) . '">'; // For debugging purposes, remove in production
?>