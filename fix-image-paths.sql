-- Quick fix: update gallery image paths from .png to .jpg
-- Run this once on the live database to fix broken images
UPDATE `gallery_items` SET `image_path` = REPLACE(`image_path`, '.png', '.jpg') WHERE `image_path` LIKE '%.png';
