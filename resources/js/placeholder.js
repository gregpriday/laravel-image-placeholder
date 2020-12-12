import {Delaunay} from "d3-delaunay";
import pako from "pako";

export function displayPlaceholder() {
  const canvases = document.querySelectorAll('canvas.image-placeholder')
  for(let i = 0; i < canvases.length; i++) {
    let canvas = canvases[i]
    if(canvas.hasAttribute('data-points') && !canvas.classList.contains('image-placeholder-loaded')) {
      let data = pako.inflateRaw(Buffer.from(canvas.getAttribute('data-points'), 'base64'), {to: 'string'});

      // Convert the data into a format that's more like how we put it in.
      let points = data.split('|').map((point) => {
        return [
          // Here we parse out the actual point coordiantes
          point.substr(6).split(',').map((v) => {
            let cInt = parseInt(v, 36)
            return [
              cInt >> 10,
              cInt & 0x3FF,
              point.substr(0, 6),
            ]
          })
        ]
      }).flat(2)

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

      const context = canvas.getContext('2d')
      context.filter = 'blur(10px)'

      points.forEach((p, i) => {
        let poly = voronoi.cellPolygon(i)
        if (poly === null) return

        context.fillStyle = '#' + p[2]
        context.strokeStyle = '#' + p[2]
        context.lineWidth = 20;
        context.beginPath()

        poly.forEach((pp, j) => {
          if(j == 0) context.moveTo(pp[0]/scale[0], pp[1]/scale[1])
          else context.lineTo(pp[0]/scale[0], pp[1]/scale[1])
        })

        context.closePath()
        context.fill()
        context.stroke()
      })

      canvas.classList.add('image-placeholder-loaded')
    }
  }
}