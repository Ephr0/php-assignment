Php is required to run this website on local machine. Run "php -S localhost:8000" and visit localhost:8000 to view website.
(Copied from assignment details)
# Features

This PHP-based website (using JavaScript mainly for asynchronous data loading via AJAX/Fetch only) identifies users via sessions and allows them to submit projects, vote on them, and (if they are admins) manage and maintain the submitted projects.

## Project List
- Guests and logged-in users can view the list of published projects.
- Users can see project details and the number of votes each project has received.
- Projects can be filtered by category.

## Authentication
- Guests can register or log in.
- Logged-in users can log out.

## Project Submission
- Logged-in users can submit project ideas through a form.
- An admin reviews each submission and either:
  - Approves it (publishing it), or
  - Rejects it (extra task: sends it back for rework).

## Voting
- Logged-in users can vote on published projects.
- A user may cast **up to 3 votes per category**.
- A user may vote **only once per project**:
  - All three votes cannot be cast on the same project.
  - Voting on one’s own project is allowed.
- Voting is open for **two weeks** after a project is published.
- Votes can be withdrawn during this two-week period.
- After voting closes, votes remain permanently attached to the project.

## Statistics
- Admins can access statistics on projects.
- Example statistics include identifying leading projects in each categor

