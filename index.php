<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <title></title>
    <style type="text/css">
     body{
      margin: 0;
      padding: 0;
     }
    </style>
    <script type="text/javascript" src="com/CanvasInput/CanvasInput.js"></script> 
    <script type="text/javascript" src="com/xml/XMLPopulate.js" ></script>
    <script type="text/javascript" src="com/img/ImageLoader.js"></script>
    <script tppe="text/javascript" src="com/iso/Isometric.js"></script>
    <script type="text/javascript">

    window.requestAnimFrame = (function() {
      return window.requestAnimationFrame || 
      window.webkitRequestAnimationFrame  || 
      window.mozRequestAnimationFrame     || 
      window.oRequestAnimationFrame       ||  
      window.msRequestAnimationFrame      || 
      function(callback, element) {
        window.setTimeout(callback, 1000 / 60);
      };
    })();

    function launch() {

      var XML = new XMLPopulate();
      var imgLoader = new ImageLoader();

      XML.loadXML('com/xml/XMLFiles.php?folder=img/ground/');
      var ground_dict = XML.getContent('files', 'file');
      var ground_images = imgLoader.loadImageArray('img/ground/', ground_dict);

      XML.loadXML('com/xml/XMLFiles.php?folder=img/city/');
      var city_dict = XML.getContent('files','file');
      var city_images = imgLoader.loadImageArray('img/city/', city_dict);

      XML.loadXML('com/xml/XMLFiles.php?folder=img/gui/');
      var gui_images = imgLoader.loadImageArray('img/gui/', XML.getContent('files','file'));
      
      var loadTimer = setInterval(loadAll, 100);

      function loadAll() {
        if (imgLoader.loaded === imgLoader.to_load) {
          clearInterval(loadTimer);
          var game = new main();
          XML.loadXML('map-read.php');
          
          game.init({
            layers: [
              game.createLayer({
                zIndex: 0,
                layout: XML.getContent('ground_map','row'),
                graphics: ground_images,
                graphicsDictionary: ground_dict,
                height: 50,
                width: 25
              }),
              game.createLayer({
                zIndex: 1,
                layout: XML.getContent('steps_dates','row'),
                graphics: city_images,
                graphicsDictionary: city_dict,
                height: 50,
                width: 25,
                zero_is_blank: true,
                alpha_mouse_behind: true,
                heightMap: XML.getContent('ground_height','row'),
                objectShadow: true,
                hideGraphics: {
                  rangeMin: 0,
                  rangeMax: 6,
                  graphic: '7-normal.png'
                },
                applyIneractions: true
              })
            ],
            gui: gui_images
          });
        }
      }
    }


    function main() {
      self = this;
      var mapLayers = [];
      var gui = [];
      var tile_coordinates = {};
      var mouse_coordinates = {};

      var canvas = document.createElement('canvas');
      canvas.width = 706;
      canvas.height = 425;
      canvas.style.border = "#333 2px solid";
      canvas.style.background = "url(img/canvas-background.jpg)"
      var context = canvas.getContext('2d');
      document.body.appendChild(canvas);

      var input = new CanvasInput(document, canvas);

      input.keyboard(function(pressed) {
        switch(pressed) {
          case 38:
            self.keyCommand(1);
          break;
          case 39:
            self.keyCommand(2);
          break;
          case 40:
            self.keyCommand(3);
          break;
          case 37:
            self.keyCommand(4);
          break;
          case 65:
            self.keyCommand(5);
          break;
          case 83:
            self.keyCommand(6);
          break;
          case 49:
            self.keyCommand(7);
          break;
          case 50:
            self.keyCommand(8);
          break;
          case 81:
            self.keyCommand(9);
          break;
        }
      });

      this.keyCommand = function(dir) {
        switch(dir) {
          case 1:
            mapLayers.map(function(layer) {
              layer.draw_y += 20; 
            });
          break;
          case 2:
            mapLayers.map(function(layer) {
              layer.draw_x -= 20; 
            });
          break;
          case 3:
            mapLayers.map(function(layer) {
              layer.draw_y -= 20; 
            });
          break;
          case 4:
            mapLayers.map(function(layer) {
              layer.draw_x += 20; 
            });
          break;
          case 5:
            mapLayers.map(function(layer) {
              layer.setZoom("out");
              layer.align("h-center",canvas.width);
              layer.align("v-center",canvas.height);
            });
          break;
          case 6:
            mapLayers.map(function(layer) {
              layer.setZoom("in");
              layer.align("h-center",canvas.width);
              layer.align("v-center",canvas.height);
            })
          break;
          case 7:
            mapLayers.map(function(layer) {
              if (layer.tilesHide !== null) {
                layer.hideGraphics(true);
              }
              if (layer.objectShadows !== null) {
                layer.applyObjectShadow(true);
              }
            });
          break;
          case 8:
          mapLayers.map(function(layer) {
            if(layer.tilesHide !== null) {
              layer.hideGraphics(false);
            }
            if (layer.objectShadows !== null) {
              layer.applyObjectShadow(false);
            }
          });
          break;
          case 9:
            mapLayers.map(function(layer) {
              layer.rotate("left");
            });
          break;
        }
        requestAnimFrame(self.draw);
      }

      this.draw = function() {
       context.clearRect(0,0,canvas.width,canvas.height);
       mapLayers.map(function(layer) {
         layer.draw();
       });
       context.fillStyle = "rgb(255,255,255)";
       if (tile_coordinates.x < mapLayers[0].getLayout().length && tile_coordinates.x >= 0 && tile_coordinates.y < mapLayers[0].getLayout().length && tile_coordinates.y >= 0) {
         mapLayers.map( function(layer) {
          if (layer.applyIneractions) {
            if (layer.getTile([tile_coordinates.x],[tile_coordinates.y]) > 0) {
             context.drawImage(gui["popup-box.png"],mouse_coordinates.x,mouse_coordinates.y);
             context.font = "8pt Arial";
             context.fillText("Hover box",mouse_coordinates.x+14,mouse_coordinates.y+25);
            }
          }
         });
       }
      }
      
      this.createLayer = function(settings) {
       layer = new Isometric(context, settings.height, settings.width, settings.layout, settings.graphics, settings.graphicsDictionary);
       layer.setZoom(1);
       layer.zero_is_blank = settings.zero_is_blank;
       layer.alpha_mouse_behind = settings.alpha_mouse_behind;
       if(settings.objectShadow) {
         layer.applyObjectShadow(settings.objectShadow);
       }
       if (settings.heightMap) {
         layer.stack_tiles(settings.heightMap, 1);
       }
       if(settings.hideGraphics) {
         layer.hideGraphics(true, {
          rangeStart: settings.hideGraphics.rangeMin, 
          rangeEnd: settings.hideGraphics.rangeMax, 
          graphic: settings.hideGraphics.graphic
         });
       }

       if(settings.applyIneractions) {
         layer.applyIneractions = settings.applyIneractions;
         input.mouse_move(function(coords) {
          tile_coordinates = layer.applyMouse(coords.x,coords.y);
          mouse_coordinates = coords;
          requestAnimFrame(self.draw);
         });  
         input.mobile(function(coords) {
          tile_coordinates = layer.applyMouse(coords.x,coords.y);
          mouse_coordinates = coords;
          requestAnimFrame(self.draw);
         });
       }

       layer.align("h-center", canvas.width);
       layer.align("v-center", canvas.height);
       return layer;
      }


      this.init = function(settings) {
       mapLayers = settings.layers;
       gui = settings.gui;
       this.draw();
      }
      
    }
    </script>
  </head>
  <body onLoad="launch()">  
  </body>
</html>