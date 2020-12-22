/**
 * Create the SVG for this
 * @param image
 * @returns {string}
 */
function setImageSvg(image) {
  const imageWidth = parseInt(image.getAttribute('width'))
  const imageHeight = parseInt(image.getAttribute('height'))
  const imageData = image.getAttribute('data-placeholder');
  const blurRadius = Math.floor(imageWidth > imageHeight ? imageWidth / 32 : imageHeight / 32);

  const canvas = document.createElement('canvas');
  let placeholder = new Image();
  placeholder.onload = function(){
    let width = placeholder.width;
    let height = placeholder.height;
    const pxWidth = imageWidth/width
    const pxHeight = imageHeight/height

    canvas.getContext('2d').drawImage(placeholder, 0, 0, width, height);

    // Lets start drawing the canvas
    let svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + imageWidth + ' ' + imageHeight + '" width="' + imageWidth + '" height="' + imageHeight + '">';
    // Add the filter definition by Taylor Hunt - https://codepen.io/tigt/post/fixing-the-white-glow-in-the-css-blur-filter
    svg += '<filter id="better-blur" x="0" y="0" width="1" height="1"><feGaussianBlur stdDeviation="' + blurRadius + '" result="blurred"/><feMorphology in="blurred" operator="dilate" radius="' + blurRadius + '" result="expanded"/><feMerge><feMergeNode in="expanded"/><feMergeNode in="blurred"/></feMerge></filter>'
    svg += '<g id="voronoi" filter="url(#better-blur)">'

    // Add all the pixel blocks
    let color
    for(let x = 0; x < width; x++) {
      for(let y = 0; y < height; y++) {
        color = 'rgb(' + canvas.getContext('2d').getImageData(x, y, 1, 1).data.slice(0, 3).join(',') + ')'
        svg += '<rect ' +
          'width="' +pxWidth + '" ' +
          'height="' + pxHeight + '" ' +
          'x="' + x*pxWidth + '" ' +
          'y="' + y*pxHeight + '" ' +
          'style="fill:' + color + '; stroke-width: 1; stroke:' + color + '" />'
      }
    }
    svg += '</g></svg>';

    image.style.backgroundImage = 'url("data:image/svg+xml;base64,' + btoa(svg) + '")'
  }
  placeholder.src = 'data:image/png;base64,' + imageData;
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
    setImageSvg(image)
    // image.style.backgroundImage = "url('data:image/svg+xml;base64," + btoa(getImageSvg(image)) + "')"
    image.removeAttribute('data-placeholder')
  }
}