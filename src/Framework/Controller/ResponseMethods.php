<?php

declare(strict_types=1);

namespace Core\Framework\Controller;

use Core\Framework\Autowire\CurrentRequest;
use Core\Framework\DependencyInjection\ServiceContainer;
use Exception;
use Northrook\Filesystem\URL;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\HttpFoundation\{BinaryFileResponse,
    File,
    JsonResponse,
    RedirectResponse,
    Request,
    Response,
    ResponseHeaderBag};
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

trait ResponseMethods
{
    use ServiceContainer, CurrentRequest;

    /**
     * Forwards the request to another controller.
     *
     * @param string $controller The controller name (a string like "App\Controller\PostController::index" or "App\Controller\PostController" if it is invokable)
     * @param array  $path
     * @param array  $query
     *
     * @return Response
     */
    protected function forward( string $controller, array $path = [], array $query = [] ) : Response
    {
        $request             = $this->serviceLocator( Request::class );
        $path['_controller'] = $controller;
        $subRequest          = $request->duplicate( $query, null, $path );

        try {
            return $this->serviceLocator( HttpKernelInterface::class )->handle(
                $subRequest,
                HttpKernelInterface::SUB_REQUEST,
            );
        }
        catch ( Exception $exception ) {
            throw $this->notFoundException( previous: $exception );
        }
    }

    /**
     * Returns a RedirectResponse to the given URL.
     *
     * @param non-empty-string|URL $url
     * @param int                  $status [302] The HTTP status code
     *
     * @return RedirectResponse
     */
    protected function redirectResponse(
        string|URL $url,
        int        $status = 302,
    ) : RedirectResponse {

        // TODO: [route] to URL
        // TODO: Validate $url->exists - update $status
        // TODO: Log failing redirects

        if ( \is_string( $url ) ) {
            $url = new URL( $url );
        }

        if ( ! $url->exists() ) {
            throw $this->notFoundException(  );
        }

        return new RedirectResponse( $url->path, $status );
    }

    /**
     * Returns a RedirectResponse to the given route with the given parameters.
     *
     * @param string $route
     * @param array  $parameters
     * @param int    $status     The HTTP status code (302 "Found" by default)
     *
     * @return RedirectResponse
     */
    protected function redirectToRoute( string $route, array $parameters = [], int $status = 302 ) : RedirectResponse
    {
        // TODO : Log redirects

        $url = $this->serviceLocator( RouterInterface::class )->generate( $route, $parameters );

        return $this->redirectResponse( $url, $status );
    }

    /**
     * Returns a {@see JsonResponse} using the {@see SerializerInterface} if available.
     *
     * - Will use the {@see SerializerInterface} assigned to {@see ServiceContainer} by default.
     * - Pass a custom {@see SerializerInterface} as the last argument to override the default.
     * - Pass `false` to use the {@see JsonResponse} built in `json_encode`.
     *
     * @param mixed                          $data
     * @param int                            $status
     * @param array                          $headers
     * @param array                          $context
     * @param null|false|SerializerInterface $serializer
     *
     * @return JsonResponse
     */
    protected function jsonResponse(
        mixed                          $data,
        int                            $status = Response::HTTP_OK,
        array                          $headers = [],
        array                          $context = [],
        SerializerInterface|null|false $serializer = null,
    ) : JsonResponse {

        if ( false !== $serializer ) {

            $serializer ??= $this->serviceLocator( SerializerInterface::class );
            $context = \array_merge( ['json_encode_options' => JsonResponse::DEFAULT_ENCODING_OPTIONS], $context );
            $json    = $serializer->serialize( $data, 'json', $context );

            return new JsonResponse( $json, $status, $headers, true );
        }

        return new JsonResponse( $data, $status, $headers );
    }

    /**
     * Return {@see File} object with original or customized
     *  file name and disposition header.
     *
     * @param SplFileInfo|string $file
     * @param ?string            $fileName
     * @param string             $disposition
     *
     * @return BinaryFileResponse
     */
    protected function fileResponse(
        SplFileInfo|string $file,
        ?string            $fileName = null,
        string             $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT,
    ) : BinaryFileResponse {
        $response = new BinaryFileResponse( $file );
        $fileName ??= $response->getFile()->getFilename();

        return $response->setContentDisposition( $disposition, $fileName );
    }

    /**
     * Returns a NotFoundHttpException.
     *
     * This will result in a 404 response code. Usage example:
     *
     *     throw $this->createNotFoundException('Page not found!');
     *
     * @param string     $message
     * @param ?Throwable $previous
     *
     * @return NotFoundHttpException
     */
    final protected function notFoundException( string $message = 'Not Found', ?Throwable $previous = null ) : NotFoundHttpException
    {
        return new NotFoundHttpException( $message, $previous );
    }
}
