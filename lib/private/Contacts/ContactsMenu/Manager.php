<?php

/**
 * @copyright 2017 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @author 2017 Christoph Wurst <christoph@winzerhof-wurst.at>
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OC\Contacts\ContactsMenu;

use OCP\Contacts\ContactsMenu\IEntry;

class Manager {

	/** @var ContactsStore */
	private $store;

	/** @var ActionProviderStore */
	private $actionProviderStore;

	/**
	 * @param ContactsStore $store
	 * @param ActionProviderStore $actionProviderStore
	 */
	public function __construct(ContactsStore $store, ActionProviderStore $actionProviderStore) {
		$this->store = $store;
		$this->actionProviderStore = $actionProviderStore;
	}

	/**
	 * @param string $userId
	 * @param string $filter
	 * @return IEntry[]
	 */
	public function getEntries($userId, $filter) {
		// TODO: contacts manager does not need a user id
		$entries = $this->store->getContacts($filter);

		$sortedEntries = $this->sortEntries($entries);
		$topEntries = array_slice($sortedEntries, 0, 25);
		$this->processEntries($topEntries);

		return $topEntries;
	}

	/**
	 * @param IEntry[] $entries
	 * @return IEntry[]
	 */
	private function sortEntries(array $entries) {
		usort($entries, function(IEntry $entryA, IEntry $entryB) {
			strcasecmp($entryA->getFullName(), $entryB->getFullName());
		});
		return $entries;
	}

	/**
	 * @param IEntry[] $entries
	 */
	private function processEntries(array $entries) {
		$providers = $this->actionProviderStore->getProviders();
		foreach ($entries as $entry) {
			foreach ($providers as $provider) {
				$provider->process($entry);
			}
		}
	}

}
