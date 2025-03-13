
CREATE TABLE Users (
    UserID INT AUTO_INCREMENT,
    Name VARCHAR(255),
    Email VARCHAR(255) UNIQUE,
    Password VARCHAR(255),
    AccomodationBudget DECIMAL(10,2),
    TransportBudget DECIMAL(10,2),
    PreferredClimate VARCHAR(100),
    PreferredActivities TEXT,
    TravelHistory TEXT,

    PRIMARY KEY (UserID)
);

CREATE TABLE Destination (
    DestinationID INT AUTO_INCREMENT,
    Location VARCHAR(255),
    Budget DECIMAL(10,2),
    Climate VARCHAR(100),
    Activities TEXT,
    PopularityScore DECIMAL(5,2),

    PRIMARY KEY (DestinationID)

);

CREATE TABLE Recommendation (
    RecommendationID INT AUTO_INCREMENT,
    UserID INT,
    DestinationID INT,
    Destination VARCHAR(255),
    RecommendationDate DATE,
    Rating DECIMAL(3,2),

    PRIMARY KEY (RecommendationID),

    FOREIGN KEY (UserID) REFERENCES Users(UserID),
    FOREIGN KEY (DestinationID) REFERENCES Destination(DestinationID)
);

CREATE TABLE UserInteraction (
    InteractionID INT AUTO_INCREMENT,
    UserID INT,
    DestinationID INT,
    Viewed BOOLEAN,
    Liked BOOLEAN,
    Booked BOOLEAN,
    InteractionDate DATETIME,
    Feedback TEXT,

    PRIMARY KEY (InteractionID),

    FOREIGN KEY (UserID) REFERENCES Users(UserID),
    FOREIGN KEY (DestinationID) REFERENCES Destination(DestinationID)
);

CREATE TABLE RLModel (
    ModelID INT AUTO_INCREMENT,
    LearningRate DECIMAL(5,4),
    RewardFunction TEXT,
    TrainingData TEXT,
    Version VARCHAR(50),

    PRIMARY KEY (ModelID)
);
