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

function drawPointsOnCanvas(points, canvas) {
  const cw = parseInt(canvas.getAttribute('width'))
  const ch = parseInt(canvas.getAttribute('height'))
  const vDimensions = cw > ch ?
    [0, 0, 1024, 1024/cw*ch] : [0, 0, 1024/ch*cw, 1024]

  const scale = [
    vDimensions[2] / cw,
    vDimensions[3] / ch
  ]

  const voronoi = Delaunay.from(
    points.map((p) => [p[0], p[1]])
  ).voronoi(vDimensions)

  // Lets start drawing the canvas
  const context = canvas.getContext('2d')
  context.filter = 'blur(10px)'
  points.forEach((p, i) => {
    let poly = voronoi.cellPolygon(i)
    if (poly === null) return

    context.fillStyle = '#' + p[2]
    context.strokeStyle = '#' + p[2]
    context.lineWidth = 15;
    context.beginPath()

    poly.forEach((pp, j) => {
      if(j === 0) context.moveTo(pp[0]/scale[0], pp[1]/scale[1])
      else context.lineTo(pp[0]/scale[0], pp[1]/scale[1])
    })

    context.closePath()
    context.fill()
    context.stroke()
  })
}

export function displayPlaceholders() {
  const canvases = document.querySelectorAll('canvas.image-placeholder')
  for(let i = 0; i < canvases.length; i++) {
    let canvas = canvases[i]

    if(canvas.hasAttribute('data-points') && !!canvas.getContext) {
      let points = extractPointsFromData(canvas.getAttribute('data-points'))
      drawPointsOnCanvas(points, canvas)
    }
  }
}