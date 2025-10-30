// =========================================================
// 1. ENVIRONMENT AND MODULE IMPORTS
// =========================================================

// Load Environment Variables (MUST be first)
require('dotenv').config();

// Core Express and Database Imports
const express = require('express');
const mongoose = require('mongoose'); 
const app = express();
const PORT = process.env.PORT || 3000;

// Import Routes
const authRoutes = require('./routes/auth'); // Import the authentication routes


// =========================================================
// 2. MIDDLEWARES
// =========================================================

// Middleware to parse incoming JSON requests (important for handling form data)
app.use(express.json()); 

// Middleware to handle URL-encoded data (basic form submissions)
app.use(express.urlencoded({ extended: true })); 


// =========================================================
// 3. DATABASE CONNECTION LOGIC
// =========================================================

const mongoURL = process.env.DB_CONNECTION_STRING; 

mongoose.connect(mongoURL)
    .then(() => {
        console.log('✅ Successfully connected to MongoDB!');
    })
    .catch(err => {
        console.error('❌ MongoDB Connection error:', err.message);
        // Exit the process if the database connection fails at startup
        process.exit(1); 
    });


// =========================================================
// 4. ROUTE INTEGRATION
// =========================================================

// Integrate the authentication routes under the '/api' base path.
// This means all routes in auth.js will be accessed via: /api/...
app.use('/api', authRoutes); 


// 5. Test Route (Verification)
app.get('/', (req, res) => {
    res.send('Smart Aid Backend Server is Running! Database connection status: OK');
});


// =========================================================
// 6. START SERVER
// =========================================================

app.listen(PORT, () => {
    console.log(`Server running at http://localhost:${PORT}`);
    console.log(`Press CTRL+C to stop the server.`);
});