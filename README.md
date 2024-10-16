# Room Booking - Component

## Description

Room Booking is a component for booking rooms. It is a simple component that allows you to book rooms.

> [!WARNING]
> This component has been created for learning purposes.
> This component is still in development and not all features have been implemented.
> This component is not yet ready for production use. Use it on your own risk.

## Features

-   Booking rooms
-   View bookings
-   Manage bookings
-   Recurring bookings
-   Room availability
-   Room booking calendar

## Prerequisites

-   Node.js (version 14 or higher)
-   pnpm (can be installed globally with `npm install -g pnpm`)
-   Joomla 5.x or higher (tested with Joomla 5.0)

## Installation

1. Clone the repository:

    ```
    git clone https://github.com/jswebschmiede/com_roombooking.git
    ```

2. Navigate to the project directory:

    ```
    cd com_roombooking
    ```

3. Install dependencies:

    ```
    pnpm install
    ```

## Usage

### Development Mode

To work in development mode and benefit from automatic reloading and copying the files to your Joomla installation:

-   install the component in Joomla (see Production Mode)
-   configure the `webpack.config.js` file with the path to your Joomla installation (default is `../../joomla`)
-   folder structure should look like this. You can change the names of the folders, important is only the structur itself.

```
joomla_dev/
    - joomla/
    - joomla_components/
        - com_roombooking/
```

-   start the development server:

```
pnpm run dev
```

### Production Mode

To create a production-ready version of your component:

```
pnpm run build
```

This creates an optimized version of the component and packages it into a ZIP file for installation in Joomla.

## Project Structure

-   `src/`: Component source code
    -   `administrator/`: Administrator area of the component
    -   `components/`: Site area of the component
    -   `media/`: Assets such as JavaScript and CSS
-   `dist/`: Compiled and optimized files (after build)
-   `webpack.config.js`: Webpack configuration
-   `tailwind.config.js`: Tailwind CSS configuration
-   `package.json`: Project dependencies and scripts

## Contributing

Contributions are welcome! Please create a pull request or open an issue for suggestions and bug reports.

## License

MIT License; see LICENSE.txt

## Todo

-   add send mail function for booking confirmation and send mail to admin.
-   add ajax booking to frontend
-   config parameters for views, like how many rooms are shown etc.
-   function to add more days to the booking
-   maybe add a payment gateway
-   generate pdf bill for the booking and send it to the customer
