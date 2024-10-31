from flask import Flask, render_template
from flask_socketio import SocketIO, emit
import cv2
import base64
import numpy as np
from ultralytics import YOLO
import threading
import time



app = Flask(__name__, static_folder="static")

socketio = SocketIO(app, cors_allowed_origins="*")  # Allow all origins for development

# Load a lighter YOLO model
model = YOLO("yolo-Weights/yolov8n.pt")

# Restrict detection to only cell phones (class index based on model)
target_class = "cell phone"
target_class_index = 67  # Update based on the model class index for "cell phone"

# Initialize frame sampling interval
frame_sample_interval = 5  # Process every 5th frame for performance
frame_count = 0

@app.route('/')
def index():
    return render_template('index.html')

def detect_cell_phone(img):
    """
    Detect cell phone in a single frame and emit alert if found.
    """
    global model, target_class, target_class_index

    # Run detection
    results = model(img)

    # Check if any detection matches "cell phone"
    for r in results:
        boxes = r.boxes
        for box in boxes:
            cls = int(box.cls[0])
            if cls == target_class_index:
                socketio.emit('alert', {'message': f'{target_class} detected! No cheating!'})
                return

@socketio.on('video_frame')
def handle_video_frame(data):
    global frame_count, frame_sample_interval

    # Decode the base64 image
    img_data = base64.b64decode(data.split(',')[1])
    np_img = np.frombuffer(img_data, np.uint8)
    img = cv2.imdecode(np_img, cv2.IMREAD_COLOR)

    # Only process every nth frame to reduce load
    frame_count += 1
    if frame_count % frame_sample_interval == 0:
        # Start detection in a separate thread
        threading.Thread(target=detect_cell_phone, args=(img,)).start()

if __name__ == '__main__':
    socketio.run(app, host="0.0.0.0", port=5000, debug=True)
