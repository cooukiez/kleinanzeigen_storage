const express = require('express');
const multer  = require('multer');
const fs = require('fs');
const path = require('path');

const app = express();
const upload = multer({ dest: 'uploads/' });

// Serve the HTML form
app.get('/', (req, res) => {
    res.sendFile(__dirname + '/index.html');
});

// Handle form submission
app.post('/upload', upload.array('image'), (req, res) => {
    const adId = req.body.ad_id;
    const title = req.body.title;
    const description = req.body.description;
    const images = req.files;

    // Create a folder with the ad ID as its name
    const folderPath = path.join(__dirname, 'advertisements', adId);

    if (!fs.existsSync(folderPath)) {
        fs.mkdirSync(folderPath);
    }

    // Move uploaded images to the new folder
    images.forEach(image => {
        const oldPath = image.path;
        const newPath = path.join(folderPath, image.originalname);
        fs.renameSync(oldPath, newPath);
    });

    // Save description to a text file
    const descriptionFilePath = path.join(folderPath, 'description.txt');
    fs.writeFileSync(descriptionFilePath, description);

    res.send('Advertisement created successfully.');
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
    console.log(`Server is running on port ${PORT}`);
});
