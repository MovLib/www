<?php

/*!
 * This file is part of {@link https://github.com/MovLib MovLib}.
 *
 * Copyright © 2013-present {@link https://movlib.org/ MovLib}.
 *
 * MovLib is free software: you can redistribute it and/or modify it under the terms of the GNU Affero General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * MovLib is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty
 * of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with MovLib.
 * If not, see {@link http://www.gnu.org/licenses/ gnu.org/licenses}.
 */
namespace MovLib\Partial\FormElement;

use \MovLib\Data\Image\AbstractImageEntity;
use \MovLib\Exception\ClientException\UnauthorizedException;

/**
 * HTML input type file form element specialized for image uploads.
 *
 * @property \MovLib\Data\Image\AbstractImageEntity $value
 *
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class InputImage extends \MovLib\Partial\FormElement\AbstractInputFile {


  // ------------------------------------------------------------------------------------------------------------------- Properties


  /**
   * Available image extensions.
   *
   * @internal We don't use image_type_to_extension() because it uses long extensions (e.g. jpeg instead of jpg).
   * @var array
   */
  protected $extensions = [
    IMAGETYPE_JPEG => "jpg",
    IMAGETYPE_PNG  => "png",
  ];

  /**
   * Insert HTML after input file HTML element.
   *
   * @var string
   */
  public $inputFileAfter;

  /**
   * The image's maximum filesize.
   *
   * @var integer
   */
  protected $maxFilesize;

  /**
   * The formatted maximum filesize.
   *
   * @see \MovLib\Presentation\AbstractBase::formatBytes()
   * @var integer
   */
  protected $maxFilesizeFormatted;

  /**
   * The maximum filesize's unit.
   *
   * @see \MovLib\Presentation\AbstractBase::formatBytes()
   * @var string
   */
  protected $maxFilesizeUnit;

  /**
   * The image's minimum height.
   *
   * @var integer
   */
  protected $minHeight;

  /**
   * The image's minimum width:
   *
   * @var integer
   */
  protected $minWidth;


  // ------------------------------------------------------------------------------------------------------------------- Magic Methods


  /**
   * Instantiate new input form element of type file.
   *
   * @param \MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP
   *   The HTTP dependency injection container.
   * @param string $id
   *   The form element's global unique identifier.
   * @param string $label
   *   The label test.
   * @param \MovLib\Data\Image\AbstractImageEntity $image
   *   The image that is to be uploaded.
   * @param array $attributes [optional]
   *   Additional attributes.
   * @param string $inputFileAfter [optional]
   *   Any content that should be included in the rendered input image form element, defaults to <code>NULL</code>.
   * @throws \MovLib\Presentation\Error\Unauthorized
   */
  public function __construct(\MovLib\Core\HTTP\DIContainerHTTP $diContainerHTTP, $id, $label, \MovLib\Data\Image\AbstractImageEntity &$image, array $attributes = null, $inputFileAfter = null) {
    // Only authenticated users are allowed to upload images.
    if ($diContainerHTTP->session->isAuthenticated === false) {
      throw new UnauthorizedException($this->intl->t(
        "You must be signed in to upload images. If you don’t have an account yet why not {0}join {sitename}{1}?.",
        [ "<a href='{$this->intl->r("/profile/join")}'>", "</a>", "sitename" => $this->config->sitename ]
      ));
    }

    // We need some JavaScript to make our input element more awesome.
    $diContainerHTTP->presenter->javascripts[] = "InputImage";

    // Initialize attributes and properties.
    $this->inputFileAfter       = $inputFileAfter;
    $this->maxFilesize          = ini_get("upload_max_filesize");
    $this->minHeight            = $image->imageHeight ?: AbstractImageEntity::IMAGE_MIN_HEIGHT;
    $this->minWidth             = $image->imageWidth  ?: AbstractImageEntity::IMAGE_MIN_WIDTH;
    $this->maxFilesizeFormatted = $diContainerHTTP->intl->formatBytes($this->maxFilesize);

    $attributes["#help-popup"] = $diContainerHTTP->intl->t("Image must be larger than {width} × {height} pixels and less than {size}. Allowed image types: JPG and PNG", [
      "width"  => $this->minWidth,
      "height" => $this->minHeight,
      "size"   => $this->maxFilesizeFormatted,
    ]);

    parent::__construct($diContainerHTTP, $id, $label, $image, $attributes);

    // Translate some error messages right away, we need them in render() and in validate()
    $this->errorMessages = [
      "preview" => $this->intl->t("The image you see is only a preview, you still have to submit the form."),
      "large"   => $this->intl->t("The image you are trying to upload is too large, it must be {size} or smaller.", [
        "size" => $this->maxFilesizeFormatted,
      ]),
      "quality" => $this->intl->t(
        "New images should have a better quality than already existing images, this includes the resolution. The " .
        "current image’s resolution is {width} × {height} and your new image’s is {width_new} × {height_new} pixels. " .
        "Please confirm that your upload has a better quality than the existing one despite the fact of smaller dimensions.",
        [ "width" => $this->value->imageWidth, "height" => $this->value->imageHeight ]
      ),
      "small"   => $this->intl->t("The image is too small, it must be larger than {width} × {height} pixels.", [
        "height" => $this->minHeight,
        "width"  => $this->minWidth,
      ]),
      "type"    => $this->intl->t("Unsupported image type and/or corrupt image, the following types are supported: JPG and PNG"),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function __toString() {
    // @devStart
    // @codeCoverageIgnoreStart
    try {
    // @codeCoverageIgnoreEnd
    // @devEnd
      $JSON     = json_encode($this->errorMessages);
      $height   = $this->value->imageHeight ? " data-height='{$this->value->imageHeight}'" : null;
      $width    = $this->value->imageWidth  ? " data-width='{$this->value->imageWidth}'"   : null;

      return
        "<div class='inputimage r' data-max-filesize='{$this->maxFilesize}' data-min-height='{$this->minHeight}' data-min-width='{$this->minWidth}'{$height}{$width}>" .
          "<script type='application/json'>{$JSON}</script>" .
          "<div class='s s2 preview'>{$this->presenter->img($this->value->imageGetStyle("s2"), [ "class" => "preview" ], false)}</div>" .
          "<div class='s s8'>{$this->required}{$this->helpPopup}<label for='{$this->id}'>{$this->label}</label>" .
            "<span class='btn input-file'><span aria-hidden='true'>{$this->intl->t("Choose Image …")}</span>" .
              "<input id='{$this->id}' name='{$this->id}' type='file' accept='image/jpeg,image/png'{$this->expandTagAttributes($this->attributes)}>" .
            "</span>{$this->inputFileAfter}" .
          "</div>" .
        "</div>"
      ;
    // @devStart
    // @codeCoverageIgnoreStart
    }
    catch (\Exception $e) {
      return $this->calloutError("<pre>{$e}</pre>", "Stacktrace");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd
  }


  // ------------------------------------------------------------------------------------------------------------------- Methods


  /**
   * Validate the uploaded image.
   *
   * @param \MovLib\Data\UploadedFile $uploadedImage
   *   The uploaded image to validate.
   * @param null|array $errors
   *   Array used to collect error messages.
   * @return this
   */
  protected function validateValue($uploadedImage, &$errors){

    // Gather meta information about the uploaded image, getimagesize() will fail if this isn't a valid image.
    list($width, $height, $type) = getimagesize($uploadedImage->path);

    // Check if this is really an image of type JPEG or PNG.
    if ($width <= 0 || $height <= 0 || ($type !== IMAGETYPE_JPEG && $type !== IMAGETYPE_PNG)) {
      $errors[self::ERROR_TYPE] = $this->errorMessages[self::ERROR_TYPE];
    }
    // Check dimension constrains.
    elseif ($height < $this->minHeight || $width < $this->minWidth) {
      $errors[self::ERROR_SMALL] = $this->errorMessages[self::ERROR_SMALL];
    }
    // An image should only be replaced with another image if the resolution is greater than the previous resolution.
    // Of course there are situations where this is not the case, we still have to tell the user.
    //
    // @internal
    //   You have to make sure that the user doesn't have to reupload this image in your presentation class by rendering
    //   a form with a confirmation dialog.
    // @todo @Richard
    //   Think about a way to solve this kind of problem once and for all. Maybe with a ConfirmationException which is
    //   catched in main.php?
    elseif ($height < $this->value->imageHeight || $width < $this->value->imageWidth) {
      $errors[self::ERROR_QUALITY] = str_replace([ "{width_new}", "{height_new}" ], [ $width, $height ], $this->errorMessages[self::ERROR_QUALITY]);
    }
    // Everything is valid, export new image properties to the concrete image class.
    else {
      $this->value->imageSaveTemporary($uploadedImage, $width, $height, $this->extensions[$type]);
    }

    return $this->value;
  }

}
