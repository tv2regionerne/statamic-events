import PublishForm from './components/Publish/PublishForm.vue'
import EventsListing from './components/Listing/EventsListing.vue'
import TriggerEventButton from './components/fieldtypes/TriggerEventButton.vue'

Statamic.$components.register('statamic-events-publish-form', PublishForm)
Statamic.$components.register('statamic-events-listing', EventsListing)
Statamic.$components.register('statamic-events-trigger-button-fieldtype', TriggerEventButton)
