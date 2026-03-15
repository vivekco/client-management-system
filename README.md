
# CSV Client Management System with Duplicate Detection

## Project Overview

This project implements a Laravel-based client management system that allows users to:

- Import client data from CSV files
- Automatically detect duplicate records
- View and manage duplicate groups
- Export filtered client data back to CSV

The system is designed to handle large datasets efficiently using batch processing and queue-based imports.

---

# Features

## CSV Import
- Upload CSV files containing client data
- Required columns:
  - company_name
  - email
  - phone_number
- Row-level validation before insertion
- Invalid rows are skipped and logged
- Import runs in background queue jobs
- Batch processing for large CSV files

## Duplicate Detection

A record is considered a duplicate when another record exists with the same:

- company_name
- email
- phone_number

Duplicate logic:

First occurrence → Unique  
Second occurrence → Duplicate  
Further occurrences → Duplicate  

Duplicates are grouped using:

duplicate_group_id

This allows viewing related duplicate records.

---

## Duplicate Group Management

Duplicate records are organized into groups.

Capabilities include:
- listing duplicate groups
- inspecting clients within a duplicate group
- identifying related duplicate records

---

## CSV Export

Export options include:

- export all clients
- export only unique clients
- export only duplicate clients

Exports use streamed CSV responses to support large datasets.

---

# Technology Stack

- PHP 8+
- Laravel
- MySQL
- PHPUnit

---

# Prerequisites

Make sure the following are installed:

- PHP 8.1+
- Composer
- MySQL
- Git

---

# Installation

## Clone Repository

git clone <repository-url>
cd client-manager

## Install Dependencies

composer install

## Create Environment File

cp .env.example .env

## Configure Environment

Update database credentials in `.env`:

APP_NAME=ClientManager
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://localhost

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=client_manager
DB_USERNAME=root
DB_PASSWORD=

QUEUE_CONNECTION=database

## Generate Application Key

php artisan key:generate

## Create Queue Table

php artisan queue:table

## Run Migrations

php artisan migrate

## Seed Database (Optional)

php artisan db:seed

or

php artisan migrate:fresh --seed

## Start Application

php artisan serve

Application URL:

http://localhost:8000

---

# Queue Setup

CSV imports are processed asynchronously.

Run a queue worker in another terminal:

php artisan queue:work

---

# API Endpoints

## Clients

GET /api/clients  
GET /api/clients?unique=1  
GET /api/clients?duplicates=1  

## Export

GET /api/clients/export  
GET /api/clients/export?unique=1  
GET /api/clients/export?duplicates=1  

## Import CSV

POST /api/clients/import

Form Data:
file = clients.csv

Response:

{
  "status": "success",
  "message": "CSV import started"
}

## Duplicate Groups

GET /api/duplicate-groups  
GET /api/duplicate-groups/{id}

---

# CSV Format

company_name,email,phone_number

Example:

ABC Pvt Ltd,test1@example.com,9801234567
XYZ Traders,test2@example.com,9801234568

---

# Running Tests

Run all tests:

php artisan test

Run feature tests:

php artisan test --testsuite=Feature

Run unit tests:

php artisan test --testsuite=Unit

Run specific test:

php artisan test --filter=DuplicateServiceTest

---

# Architecture

Controller  
↓  
Service Layer  
↓  
Models  
↓  
Database  

Main Services:

ClientService  
CsvImportService  
DuplicateService  

Queue Job:

ImportClientsJob

---

# Database Tables

Clients
- company_name
- email
- phone_number
- signature
- duplicate_group_id

DuplicateGroups
- id
- signature

ImportLogs
- file_name
- row_number
- data
- errors

ImportSummaries
- file_name
- total_rows
- inserted
- skipped
- duplicates

---

# Postman Collection Link
https://web.postman.co/workspace/My-Workspace~9e3c7570-ee24-4b25-87bc-75e53039784b/collection/14070377-406e50d5-e84a-4ffa-b12d-3e0ec26f453d?action=share&source=copy-link&creator=14070377

# Possible Improvements

Future improvements could include:

- duplicate merge workflow
- frontend dashboard (Vue or React)
- import progress tracking
- authentication for APIs

---

# Submission Contents

This repository includes:

- Laravel application source code
- migrations
- factories
- seeders
- tests
- API implementation
- Postman collection
- sample CSV files
- documentation

---

# Notes

This implementation focuses on backend functionality required by the challenge and prioritizes scalability, maintainability, and clean architecture.
