from ultralytics import YOLO
import cv2
import math
import requests  # For sending alerts to the server

# Start webcam
cap = cv2.VideoCapture(0)
cap.set(3, 1920)
cap.set(4, 1080)

# Load YOLO model
model = YOLO("yolo-Weights/yolov8n.pt")

# Object classes (including cell phone)
classNames = ["person", "bicycle", "car", "motorbike", "aeroplane", "bus", "train", "truck", "boat",
              "fire hydrant", "stop sign", "parking meter", "bench", "bird", "cat",
              "dog", "horse", "sheep", "cow", "elephant", "bear", "zebra", "giraffe", "backpack", "umbrella",
              "handbag", "tie", "suitcase", "frisbee", "skis", "snowboard", "sports ball", "kite", "baseball bat",
              "baseball glove", "skateboard", "surfboard", "tennis racket", "bottle", "wine glass", "cup",
              "fork", "knife", "spoon", "bowl", "banana", "apple", "sandwich", "orange", "broccoli",
              "carrot", "hot dog", "pizza", "donut", "cake", "chair", "sofa", "pottedplant", "bed",
              "diningtable", "toilet", "tvmonitor", "laptop", "mouse", "keyboard", "cell phone",
              "microwave", "oven", "toaster", "sink", "refrigerator", "book", "clock", "vase", "scissors",
              "teddy bear", "hair drier", "toothbrush", "smartphone"
              ]

# Replace with your server URL if needed
SERVER_URL = "https://your-server.com"

# Function to send an alert to the server when a cell phone is detected
def send_alert(user_id):
    try:
        # Sending a POST request to the server
        response = requests.post(f"{SERVER_URL}/alert", data={"message": "Cell phone detected!", "user_id": user_id})
        print("Alert sent to the server:", response.status_code)
    except Exception as e:
        print("Error sending alert:", e)

while True:
    success, img = cap.read()
    results = model(img, stream=True)

    # Coordinates
    for r in results:
        boxes = r.boxes

        for box in boxes:
            # Bounding box
            x1, y1, x2, y2 = box.xyxy[0]
            x1, y1, x2, y2 = int(x1), int(y1), int(x2), int(y2)  # Convert to int values

            # Put box in cam
            cv2.rectangle(img, (x1, y1), (x2, y2), (255, 0, 255), 3)

            # Confidence
            confidence = math.ceil((box.conf[0] * 100)) / 100

            # Class name
            cls = int(box.cls[0])

            # Object details
            org = [x1, y1]
            font = cv2.FONT_HERSHEY_SIMPLEX
            fontScale = 1
            color = (255, 0, 0)
            thickness = 2

            cv2.putText(img, classNames[cls], org, font, fontScale, color, thickness)

            # Check if the detected object is a cell phone
            if classNames[cls] == "cell phone":
                # Alert on screen
                cv2.putText(img, "Cell phone detected! No cheating!", (x1, y1 - 10), font, fontScale, (0, 0, 255), thickness)

                # Log to the console
                print("Alert: Cell phone detected! No cheating allowed.")

                # Send alert to the server (you can pass the user's ID or session here)
                send_alert(user_id="current_user_id")  # Replace with the actual user ID or session ID

                # Optionally: Pause the exam or trigger any backend action
                # requests.post(f"{SERVER_URL}/pause-exam", data={"user_id": "current_user_id"})

    # Display webcam feed
    cv2.imshow('Webcam', img)
    
    # Break the loop if 'q' is pressed
    if cv2.waitKey(1) == ord('q'):
        break

cap.release()
cv2.destroyAllWindows()
