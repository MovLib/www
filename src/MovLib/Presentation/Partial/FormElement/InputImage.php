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
namespace MovLib\Presentation\Partial\FormElement;

use \MovLib\Data\Image\AbstractBaseImage;
use \MovLib\Presentation\Error\Unauthorized;

/**
 * HTML input type file form element specialized for image uploads.
 *
 * @property \MovLib\Data\Image\AbstractImage $value
 *
 * @link http://www.whatwg.org/specs/web-apps/current-work/multipage/the-input-element.html#attr-input-type
 * @link https://developer.mozilla.org/en-US/docs/Web/HTML/Element/Input
 * @author Richard Fussenegger <richard@fussenegger.info>
 * @copyright © 2013 MovLib
 * @license http://www.gnu.org/licenses/agpl.html AGPL-3.0
 * @link https://movlib.org/
 * @since 0.0.1-dev
 */
final class InputImage extends \MovLib\Presentation\Partial\FormElement\AbstractInputFile {


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
   * @global \MovLib\Data\I18n $i18n
   * @global \MovLib\Kernel $kernel
   * @global \MovLib\Data\User\Session $session
   * @param string $id
   *   The form element's global unique identifier.
   * @param string $label
   *   The label test.
   * @param \MovLib\Data\Image\AbstractBaseImage $concreteImage
   *   The abstract image instance that's responsible for this image.
   * @param array $attributes [optional]
   *   Additional attributes.
   * @throws \MovLib\Presentation\Error\Unauthorized
   */
  public function __construct($id, $label, $concreteImage, array $attributes = null) {
    global $i18n, $kernel, $session;

    // @devStart
    // @codeCoverageIgnoreStart
    if (!($concreteImage instanceof AbstractBaseImage)) {
      throw new \LogicException("The instance of image passed to the image input element must be of \\MovLib\\Data\\Image\\AbstractBaseImage.");
    }
    // @codeCoverageIgnoreEnd
    // @devEnd

    // Only authenticated users are allowed to upload images.
    if ($session->isAuthenticated === false) {
      throw new Unauthorized($i18n->t(
        "You must be signed in to upload images. If you don’t have an account yet why not {0}join {sitename}{1}?.", [
          "<a href='{$i18n->r("/profile/join")}'>", "</a>", "sitename" => $kernel->siteName,
        ]
      ));
    }

    // Instantiate the input element.
    parent::__construct($id, $label, $attributes);

    // We need some JavaScript to make our input element more awesome.
    $kernel->javascripts[] = "InputImage";

    // Initialize attributes and properties.
    $this->image       = $concreteImage;
    $this->maxFilesize = ini_get("upload_max_filesize");
    $this->minHeight   = $this->image->height ? : AbstractBaseImage::IMAGE_MIN_HEIGHT;
    $this->minWidth    = $this->image->width ? : AbstractBaseImage::IMAGE_MIN_WIDTH;

    list($this->maxFilesizeFormatted, $this->maxFilesizeUnit) = $this->formatBytes($this->maxFilesize);

    $this->setHelp($i18n->t("Image must be larger than {width} × {height} pixels and less than {size,number,integer} {unit}. Allowed image types: JPG and PNG", [
      "width"  => $this->minWidth,
      "height" => $this->minHeight,
      "size"   => $this->maxFilesizeFormatted,
      "unit"   => $this->maxFilesizeUnit,
    ]));

    // Translate some error messages right away, we need them in render() and in validate()
    $this->errorMessages = [
      "preview" => $i18n->t("The image you see is only a preview, you still have to submit the form."),
      "large"   => $i18n->t("The image you are trying to upload is too large, it must be {size,number,integer} {unit} or smaller.", [
        "size" => $this->maxFilesizeFormatted,
        "unit" => $this->maxFilesizeUnit,
      ]),
      "quality" => $i18n->t(
        "New images should have a better quality than already existing images, this includes the resolution. The " .
        "current image’s resolution is {width} × {height} and your new image’s is {width_new} × {height_new} pixels. " .
        "Please confirm that your upload has a better quality than the existing one despite the fact of smaller dimensions.",
        [ "width" => $this->image->width, "height" => $this->image->height ]
      ),
      "small"   => $i18n->t("The image is too small, it must be larger than {width} × {height} pixels.", [
        "height" => $this->minHeight,
        "width"  => $this->minWidth,
      ]),
      "type"    => $i18n->t("Unsupported image type and/or corrupt image, the following types are supported: JPG and PNG"),
    ];
  }

  /**
   * @inheritdoc
   * @global \MovLib\Data\I18n $i18n
   */
  protected function render() {
    global $i18n;

    $JSON     = json_encode($this->errorMessages);
    $height   = $this->image->height ? " data-height='{$this->image->height}'" : null;
    $width    = $this->image->width  ? " data-width='{$this->image->width}'"   : null;

    return
      "<div class='inputimage r' data-max-filesize='{$this->maxFilesize}' data-min-height='{$this->minHeight}' data-min-width='{$this->minWidth}'{$height}{$width}>" .
        "<script type='application/json'>{$JSON}</script>" .
        "<div class='s s2 preview'>{$this->getImage($this->image->getStyle(AbstractBaseImage::STYLE_SPAN_02), false)}</div>" .
        "<div class='s s8'>{$this->required}{$this->help}<label for='{$this->id}'>{$this->label}</label>" .
          "<span class='btn input-file'><span aria-hidden='true'>{$i18n->t("Choose Image …")}</span>" .
            "<input id='{$this->id}' name='{$this->id}' type='file' accept='image/jpeg,image/png'{$this->expandTagAttributes($this->attributes)}>" .
          "</span>{$this->inputFileAfter}" .
        "</div>" .
      "</div>"
    ;
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
      return $this;
    }

    // Check dimension constrains.
    if ($height < $this->minHeight || $width < $this->minWidth) {
      $errors[self::ERROR_SMALL] = $this->errorMessages[self::ERROR_SMALL];
      return $this;
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
    if ($this->height < $this->image->height || $this->width < $this->image->width) {
      $errors[self::ERROR_QUALITY] = str_replace([ "{width_new}", "{height_new}" ], [ $this->width, $this->height ], $this->errorMessages[self::ERROR_QUALITY]);
      return $this;
    }

    return $this;
  }

}
