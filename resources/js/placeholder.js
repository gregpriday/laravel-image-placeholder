import {Delaunay} from "d3-delaunay";

const CHARS = '!#$%&()*+-.0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[]^_`abcdefghijklmnopqrstuvwxyz{}~ ';

/**
 * Convert an encoded string back into an integer
 * @param encoded
 * @returns {*}
 */
function parseEncodedInt(encoded) {
  return encoded.split('').reverse().map((c, i) => {
    return CHARS.indexOf(c) * Math.pow(CHARS.length, i)
  }).reduce((a, b) => a + b, 0)
}

function extractPointsFromData(data) {
  // Convert the data into a format that's more like how we put it in.
  return data.split('|').map((point) => {
    let pdata = point.split(',')

    // The color is the first element of the array
    let color = parseEncodedInt(pdata[0]).toString(16).padStart(6, '0')

    return [
      // Here we parse out the actual point coordiantes
      pdata.slice(1).map((v) => {
        let cInt = parseEncodedInt(v)
        return [
          // The x position is in the first 10 bits
          cInt >> 10,
          // The y position is in the next 10
          cInt & 0x3FF,
          color,
        ]
      })
    ]
  }).flat(2)
}

function getPointsSVG(points, image) {
  const imageWidth = parseInt(image.getAttribute('width'))
  const imageHeight = parseInt(image.getAttribute('height'))

  const vDimensions = imageWidth > imageHeight ?
    [0, 0, 1024, 1024/imageWidth*imageHeight] : [0, 0, 1024/imageHeight*imageWidth, 1024]

  const scale = [
    vDimensions[2] / imageWidth,
    vDimensions[3] / imageHeight
  ]

  const voronoi = Delaunay.from(
    points.map((p) => [p[0], p[1]])
  ).voronoi(vDimensions)

  // Lets start drawing the canvas
  let svg = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ' + imageWidth + ' ' + imageHeight + '" width="' + imageWidth + '" height="' + imageHeight + '">';

  // Add the filter definition
  const blurRadius = 12
  svg += '<filter id="better-blur" x="0" y="0" width="1" height="1"><feGaussianBlur stdDeviation="' + blurRadius + '" result="blurred"/><feMorphology in="blurred" operator="dilate" radius="' + blurRadius + '" result="expanded"/><feMerge><feMergeNode in="expanded"/><feMergeNode in="blurred"/></feMerge></filter>'
  svg += '<g id="voronoi" filter="url(#better-blur)">'

  svg += points.map((p, i) => {
    let poly = voronoi.cellPolygon(i)
    if (poly === null) return '';

    let d = poly.map((pp, j) => {
      return (j === 0 ? 'M' : 'L') + Math.round(pp[0]/scale[0]) + ' ' + Math.round(pp[1]/scale[1])
    }).join(' ')

    return '<path d="' + d + ' Z" stroke="#' + p[2] + '" fill="#' + p[2] + '" stroke-width="15" />'

  }).join("");
  svg += '</g></svg>';

  return svg;
}

export function displayPlaceholders() {
  const images = document.querySelectorAll('img[data-placeholder]')
  for(let i = 0; i < images.length; i++) {
    let image = images[i]
    let points = extractPointsFromData(image.getAttribute('data-placeholder'))
    let svg = getPointsSVG(points, image);

    image.style.backgroundPosition = 'center center'
    image.style.backgroundSize = 'cover'
    image.style.backgroundImage = "url('data:image/svg+xml;base64," + btoa(svg) + "')";
  }
}