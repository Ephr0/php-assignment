(copied from assignment page)
Features
This PHP-based website (using JavaScript mainly for asynchronous data loading via AJAX/Fetch only) identifies users via sessions and allows them to submit projects, vote on them, and (if they are admins) manage and maintain the submitted projects.

Project list: Guests and logged-in users can view the list of published projects, see their details, the number of votes they have received, and filter projects by category.
Authentication: Guests can register or log in; logged-in users can log out.
Project submission: Logged-in users can submit project ideas through a form. An admin reviews each submission and either approves it (publishing it) or rejects it (extra task: sends it back for rework).
Voting: Logged-in users can vote on published projects. A user may cast up to 3 votes per category, but only once per project (i.e., they cannot cast all three votes on the same project; but it is allowed to vote on their own project). Voting is possible for two weeks after a project is published; after that, voting closes. Votes can be withdrawn during that two-week period, after which they remain attached to the project.
Statistics: Admins can access statistics on projects, e.g. which projects lead in each category.
