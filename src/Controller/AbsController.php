<?php

declare(strict_types=1);

namespace src\Controller;

use src\Model\MovieModel;

/**
 * Abstract Controller Class
 * 
 * Provides helper parent methods to child class methods
 * 
 * @var MovieModel $movieModel
 * 
 */
abstract class AbsController implements IntController
{
    protected MovieModel $movieModel;

    public function __construct(MovieModel $movieModel)
    {
        $this->movieModel = $movieModel ?: new $movieModel(databaseName: "movies");
    }

    protected function validateAttribute(mixed $requestAttribute): bool
    {
        // Check if attribute is not null
        return is_null($requestAttribute);
    }

    protected function validateResource(mixed $requestAttribute): bool
    {
        // Check if resource exists in the database
        return $this->movieModel->validateMovie("movie_details", ['uid' => 'uid'], htmlspecialchars($requestAttribute));
    }

    protected function errorResponse(array|string $response, array|string $message, array|string|bool|null $data): array
    {
        $errorResponse = [
            'error' =>   $response,
            'message' => $message,
            'data' => $data
        ];

        return $errorResponse;
    }

    protected function successResponse(array|string $response, array|string $message, array|string|bool|null $data): array
    {
        $successResponse = [
            'success' =>   $response,
            'message' => $message,
            'data' => $data
        ];

        return $successResponse;
    }

    protected function sanitizeData(): array
    {
        $sanitizedData = [];

        $postData = json_decode(file_get_contents('php://input'), true);
        foreach ($postData as $postField => $postValue) {
            $sanitizedData[$postField] = filter_var($postValue, FILTER_SANITIZE_SPECIAL_CHARS);
        }
        return $sanitizedData;
    }

    protected function validateData(): array
    {
        $errors = [];
        $validatedData = [];
        $sanitizedData = $this->sanitizeData();

        // Movie ID Field
        $pattern = '/^mv[\d]{3,4}$/';
        $uid = $sanitizedData['uid'] ?? "";
        if (preg_match($pattern, $uid)) {
            $validatedData['uid'] = $uid;
        } else {
            $errors['uid'] = 'Please pass a valid movie unique id';
        }

        // Movie Title Field
        $title = $sanitizedData['title'] ?? null;
        if (is_string($title)) {
            $validatedData['title'] = $title;
        } else {
            $errors['title'] = 'Please pass a valid movie title';
        }

        // Movie Year Field
        $year = $sanitizedData['year'] ?? "";
        if (is_numeric($year)) {
            $validatedData['year'] = (int) $year;
        } else {
            $errors['year'] = 'Please pass a valid movie year';
        }

        // Movie Release Date Field 
        $released = $sanitizedData['released'] ?? "";
        if (strtotime($released) !== false) {
            $validatedData['released'] = $released;
        } else {
            $errors['released'] = 'Please pass a valid movie release date: YYYY-MM-DD';
        }

        // Movie Runtime Field
        $runtime = $sanitizedData['runtime'] ?? "";
        if (is_numeric($runtime)) {
            $validatedData['runtime'] = (string) $runtime . ' mins';
        } else {
            $errors['runtime'] = 'Please pass a valid movie runtime in minutes';
        }

        // Movie Directors Field
        $directors = $sanitizedData['directors'] ?? "";
        if (is_string($directors)) {
            $validatedData['directors'] = $directors;
        } else {
            $errors['directors'] = 'Please pass valid movie director name(s)';
        }

        // Movie Actors Field
        $actors = $sanitizedData['actors'] ?? "";
        if (is_string($actors)) {
            $validatedData['actors'] = $actors;
        } else {
            $errors['actors'] = 'Please pass valid movie actor name(s)';
        }

        // Movie Country Field
        $country = $sanitizedData['country'] ?? "";
        if (is_string($country)) {
            $validatedData['country'] = $country;
        } else {
            $errors['country'] = 'Please pass a valid movie country';
        }

        // Movie Poster Field
        $poster = $sanitizedData['poster'] ?? "";
        if (is_string($poster)) {
            $validatedData['poster'] = $poster;
        }
        $validatedData['poster'] = "";

        // Movie IMDB Field
        $imdb = $sanitizedData['imdb'] ?? "";
        if (filter_var($imdb, FILTER_VALIDATE_INT) !== false) {
            $validatedData['imdb'] = (string) $imdb . "/10";
        } else {
            $errors['imdb'] = 'Please pass a valid movie rating';
        }

        // Movie Type Field
        $type = $sanitizedData['type'] ?? "";
        if (is_string($type)) {
            $validatedData['type'] = $type;
        } else {
            $errors['type'] = 'Please pass a valid movie type';
        }

        if (!empty($errors)) {

            $errors = [
                'error' => 'Unprocessable Entity',
                'message' => 'Invalid Entries',
                'data' => $errors,
            ];

            return $errors;
        }

        return $validatedData;
    }
}
