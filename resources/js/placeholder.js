/**
 * Create the SVG for this
 * @param image
 * @returns {string}
 */
function getImageSvg(image) {
  const imageWidth = parseInt(image.getAttribute('width'))
  const imageHeight = parseInt(image.getAttribute('height'))
  const imageData = image.getAttribute('data-placeholder');

  // Lets start drawing the canvas
  let svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + imageWidth + ' ' + imageHeight + '" width="' + imageWidth + '" height="' + imageHeight + '">';

  // Add the filter definition by Taylor Hunt - https://codepen.io/tigt/post/fixing-the-white-glow-in-the-css-blur-filter
  const blurRadius = Math.floor(imageWidth > imageHeight ? imageWidth / 32 : imageHeight / 32);
  svg += '<filter id="better-blur" x="0" y="0" width="1" height="1"><feGaussianBlur stdDeviation="' + blurRadius + '" result="blurred"/><feMorphology in="blurred" operator="dilate" radius="' + blurRadius + '" result="expanded"/><feMerge><feMergeNode in="expanded"/><feMergeNode in="blurred"/></feMerge></filter>'
  svg += '<g id="voronoi" filter="url(#better-blur)">'
  svg += '<image href="data:image/png;base64,' + imageData + '" width="' + imageWidth + '" height="' + imageHeight + '" />'
  svg += '</g></svg>';

  return svg;
}

/**
 * Main function to display placeholders
 */
export function displayPlaceholders() {
  const images = document.querySelectorAll('img[data-placeholder]')
  for(let i = 0; i < images.length; i++) {
    let image = images[i]
    if (!image.getAttribute('data-placeholder')) continue;

    // Add in the background
    image.style.backgroundPosition = 'center center'
    image.style.backgroundSize = 'cover'
    image.style.backgroundImage = "url('data:image/svg+xml;base64," + btoa(getImageSvg(image)) + "')"
    image.removeAttribute('data-placeholder')
  }
}