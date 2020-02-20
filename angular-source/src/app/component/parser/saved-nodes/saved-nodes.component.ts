import {Component, EventEmitter, Input, OnInit, Output} from '@angular/core';
import {ParserRequest} from "../../../model/parser-request";

@Component({
  selector: 'app-saved-nodes',
  templateUrl: './saved-nodes.component.html',
  styleUrls: ['./saved-nodes.component.scss']
})
export class SavedNodesComponent implements OnInit {

	@Input() parserRequest: ParserRequest = null;

	@Output() onNodeSelect = new EventEmitter<ParserRequest>();

	constructor() { }

	ngOnInit() {}

	/**
	 * Show/hide modal with saved objects
	 */
	public toggleSavedObjectsModal(): void {

	};

}
