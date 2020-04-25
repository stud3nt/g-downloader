import { Component, Input, OnInit } from '@angular/core';
import { ParserRequest } from "../../../model/parser-request";
import { NodeStatus } from "../../../enum/node-status";
import { ParserService } from "../../../service/parser.service";
import { ParserNode } from "../../../model/parser-node";
import { RouterService } from "../../../service/router.service";
import { NodeLevel } from "../../../enum/node-level";
import {ToastrDataService} from "../../../service/data/toastr-data.service";

@Component({
  selector: 'app-nodes-list',
  templateUrl: './nodes-list.component.html'
})
export class NodesListComponent implements OnInit {

	@Input() parserRequest: ParserRequest;

	public NodeStatus = NodeStatus;
	public NodeLevel = NodeLevel;

	public hoverRatingNode: ParserNode = null;
	public hoverRatingValue: number = 0;

	// controller - if true, all tiles are locked (non-clickable);
	public lockTiles = false;

	public currentUrl: string = '';

	public _ratingStars = [];

	constructor(
		private parserService: ParserService,
		private toastrService: ToastrDataService,
		public routerService: RouterService
	) { }

	ngOnInit(): void {
		this.currentUrl = document.location.pathname;
		this._ratingStars = [];

		for (let x = 1; x <= 10; x++)
			this._ratingStars[x] = x;
	}

	/**
	 * Marks node with specified status;
	 *
	 * @param node
	 * @param status
	 */
	public markNode(node: ParserNode, status: string): void {
		if (status === null || node.hasStatus(NodeStatus.Waiting))
			return;
		else
			node.addStatus(NodeStatus.Waiting);

		let nodeHasStatus = node.hasStatus(status);

		node.toggleStatus(status);

        this.parserService.updateNode(node).subscribe((response) => {
            this.toastrService.addSuccess('SUCCESS', ('Node '+((nodeHasStatus) ? 'umarked.' : 'marked.')), 5);
            node.removeStatus(NodeStatus.Waiting);
		}, (error) => {
			node.removeStatus(NodeStatus.Waiting);
      this.toastrService.addError('ERROR', error);
		});
	}

	public rateNode(node: ParserNode, rating: number) {
	  if (node.hasStatus(NodeStatus.Waiting))
	    return;

	  node.personalRating = rating;

    this.parserService.updateNode(node).subscribe((response) => {
      this.toastrService.addSuccess('SUCCESS', 'Node rated.', 8);
      node.removeStatus(NodeStatus.Waiting);
    }, (error) => {
      node.removeStatus(NodeStatus.Waiting);
      this.toastrService.addError('ERROR', error);
    });
  }

	public showPersonalDescription(node: ParserNode): void {
		if (!node.personalDescription)
			return;
	}

	/**
	 * Determines class for specified node based on status;
	 *
	 * @param node
	 * @return string
	 */
	public getNodeClass(node: ParserNode): string {
		let nodeClass = 'tile tile-250';

		if (node.hasStatus(NodeStatus.Saved))
			nodeClass += ' saved';

		if (node.hasStatus(NodeStatus.Downloaded))
			nodeClass += ' downloaded';

		return nodeClass;
	}

	/**
	 * Determines node button class based on checked status;
	 *
	 * @param node
	 * @param status
	 * @param size
	 * @return string
	 */
	public getNodeButtonClass(node: ParserNode, status: string, size: string = 'normal'): string {
		let buttonClasses = 'btn';

		switch (size) {
			case 'normal':
				break;

			case 'small':
				buttonClasses += ' btn-sm';
				break;
		}

		if (node.hasStatus(status))
			buttonClasses += (' '+NodeStatus.buttonStatusClass(status));
		else
			buttonClasses += ' btn-default';

		return buttonClasses;
	}
}
