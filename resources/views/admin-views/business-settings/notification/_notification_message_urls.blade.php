  <ul class="nav nav-tabs border-0 nav--tabs nav--pills">
      <li class="nav-item">
          <a class="nav-link {{ !request('message-type') ? 'active' : '' }}" href="{{ route('admin.business-settings.notificationMessages') }}"
              aria-disabled="true">{{ translate('messages.Customer') }}</a>
      </li>
      <li class="nav-item">
          <a class="nav-link {{ request('message-type') == 'restaurant' ? 'active' : '' }}"
              href="{{ route('admin.business-settings.notificationMessages', ['message-type' => 'restaurant']) }}"
              aria-disabled="true">{{ translate('messages.Restaurant') }}</a>
      </li>
      <li class="nav-item">
          <a class="nav-link {{ request('message-type') == 'deliveryman' ? 'active' : '' }}"
              href="{{ route('admin.business-settings.notificationMessages', ['message-type' => 'deliveryman']) }}"
              aria-disabled="true">{{ translate('messages.Deliveryman') }}</a>
      </li>
  </ul>
