// Soapy arduino component, built for Arduino Mega2560.
// Copyright 2015 Drew Gottlieb

// Configuration

const int NUM_DOORS = 2;
const int NUM_LAMPS = 2;
const int NUM_RFIDS = 2;

const int PIN_DOOR[] = { 2, 3 };
const int PIN_LAMP[] = { 5, 6 };
const int SERIAL_BAUD = 9600;
const int RFID_BAUD = 9600;

// Everything else

bool doorStatus[NUM_DOORS];
bool lampStatus[NUM_LAMPS];
char *rfidBuffer[NUM_RFIDS];
int rfidBufIdx[NUM_RFIDS]; // -1 means not scanning

void setup() {
  // Serial for communicating with android tablet.

  Serial.begin(SERIAL_BAUD);

  // Pin modes for lamps and doors, and initial polling.

  for (int i = 0; i < NUM_LAMPS; i++) {
    pinMode(PIN_LAMP[i], OUTPUT);
  }

  for (int i = 0; i < NUM_DOORS; i++) {
    pinMode(PIN_DOOR[i], INPUT);
    pollDoor(i, true);
  }

  // Initialize serial ports for RFID readers.

  for (int i = 0; i < NUM_RFIDS; i++) {
    rfidBuffer[i] = new char[15];
    rfidBufIdx[i] = -1; // not scanning
  }

  switch (NUM_RFIDS) {
    case 3:
      Serial3.begin(RFID_BAUD);
    case 2:
      Serial2.begin(RFID_BAUD);
    case 1:
      Serial1.begin(RFID_BAUD);
  }
}

void pollDoor(int door) {
  pollDoor(door, false);
}

void pollDoor(int door, bool forcePrint) {
  bool status = !digitalRead(PIN_DOOR[door]);
  if (status != doorStatus[door] || forcePrint) {
    Serial.print("door[");
    Serial.print(door, DEC);
    Serial.print("]: ");
    Serial.println(status ? "open" : "closed");
    doorStatus[door] = status;
  }
}

bool startsWith(const char *prefix, const char *str) {
  return strncmp(prefix, str, strlen(prefix)) == 0;
}

void sendConfig() {
  Serial.print("num_doors: ");
  Serial.println(NUM_DOORS, DEC);

  Serial.print("num_rfid: ");
  Serial.println(NUM_RFIDS, DEC);

  Serial.print("num_lamps: ");
  Serial.println(NUM_LAMPS, DEC);
}

void parseCommand(const char *cmd) {
  if (startsWith("lamp[", cmd)) {
    int lampId = cmd[5] - '0';
    if (lampId < 0 || lampId > NUM_LAMPS - 1) {
      return;
    }
    bool on = (strcmp(cmd + 9, "on") == 0);
    digitalWrite(PIN_LAMP[lampId], on);
  } else if (strcmp("poll", cmd) == 0) {
    sendConfig();
    for (int i = 0; i < NUM_DOORS; i++) {
      pollDoor(i, true);
    }
  }
}

void scanSerial() {
  static char buffer[500];
  static int bufferPos = 0;

  int c;
  while ((c = serialRead(0)) != -1) {
    if (c == '\n' || c == '\r') {
      if (bufferPos > 0) {
        buffer[bufferPos] = '\0';
        parseCommand(buffer);
        bufferPos = 0;
      }
    } else {
      buffer[bufferPos++] = c;
    }

    if (bufferPos > sizeof(buffer) - 1) {
      bufferPos = sizeof(buffer) - 1;
    }
  }
}

void handleRfid(int rfidId, const char *buffer) {
  Serial.print("rfid[");
  Serial.print(rfidId, DEC);
  Serial.print("]: ");
  Serial.println(buffer);
}

int serialRead(int index) {
  switch (index) {
    case 0:
      return Serial.read();
    case 1:
      return Serial1.read();
    case 2:
      return Serial2.read();
    case 3:
      return Serial3.read();
    default:
      return -1;
  }
}

void scanRfid(int rfidId) {
  int c;
  while ((c = serialRead(rfidId + 1)) != -1) {
    if (rfidBufIdx[rfidId] == -1) {
      if (c == '\x02') { // stx
        rfidBufIdx[rfidId] = 0;
      } else {
        continue;
      }
    } else {
      if (c == '\x03') { // etx
        rfidBuffer[rfidId][rfidBufIdx[rfidId]] = 0;
        handleRfid(rfidId, rfidBuffer[rfidId]);
        rfidBufIdx[rfidId] = -1;
      } else {
        rfidBuffer[rfidId][rfidBufIdx[rfidId]++] = c;
      }
    }
  }
}

void loop() {
  scanSerial();

  for (int i = 0; i < NUM_RFIDS; i++) {
    scanRfid(i);
  }

  for (int i = 0; i < NUM_LAMPS; i++) {
    pollDoor(i);
  }

  delay(50);
}
