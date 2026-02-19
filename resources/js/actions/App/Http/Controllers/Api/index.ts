import EventController from './EventController'
import BookingController from './BookingController'
const Api = {
    EventController: Object.assign(EventController, EventController),
BookingController: Object.assign(BookingController, BookingController),
}

export default Api