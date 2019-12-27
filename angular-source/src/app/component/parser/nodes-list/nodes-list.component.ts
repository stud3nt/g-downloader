import { Component, EventEmitter, Input, OnInit, Output } from '@angular/core';
import { ParserRequest } from "../../../model/parser-request";
import { NodeStatus } from "../../../enum/node-status";
import { ParserService } from "../../../service/parser.service";
import { PageLoaderDataService } from "../../../service/data/page-loader-data.service";
import { ParserNode } from "../../../model/parser-node";

@Component({
  selector: 'app-nodes-list',
  templateUrl: './nodes-list.component.html'
})
export class NodesListComponent {

	@Input() parserRequest: ParserRequest;
	@Output() openChildNode = new EventEmitter<ParserNode>();

	public NodeStatus = NodeStatus;

	public lockTiles = false;

	public scrollY = 0;

	constructor(private parserService: ParserService) { }

	public openNode(childNode: ParserNode): void {
		if (!this.lockTiles) {
			this.parserRequest.resetNodes();
			this.parserRequest.level = childNode.level;
			this.parserRequest.currentNode = childNode;
			this.openChildNode.emit(childNode);
		}
	}

	/**
	 * Marks node with specified status;
	 *
	 * @param node
	 * @param status
	 */
	public markNode(node: ParserNode, status: string): void {
		if (node.hasStatus(NodeStatus.Waiting)) {
			return;
		} else {
			node.addStatus(NodeStatus.Waiting);
		}

		if (node.hasStatus(status)) {
			node.removeStatus(status);
		} else {
			node.addStatus(status);
		}

		this.parserRequest.actionNode = node;
		this.parserService.markNode(node).subscribe((response) => {
			node.removeStatus(NodeStatus.Waiting);
		}, (error) => {
			node.removeStatus(NodeStatus.Waiting);
		});
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
	 * @return string
	 */
	public getNodeButtonClass(node: ParserNode, status: string): string {
		return 'btn ' + ((node.hasStatus(status)) ? 'btn-success' : 'btn-default');
	}
}
