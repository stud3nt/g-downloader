import { Component, OnInit } from '@angular/core';
import { NavigationEnd, Router } from "@angular/router";
import { ContentHeaderDataService } from "../../service/data/content-header-data.service";
import { ParserService } from "../../service/parser.service";
import { ParserType } from "../../enum/parser-type";
import { ConfigService } from "../../service/config.service";
import { NodeLevel } from "../../enum/node-level";
import { ParserNode } from "../../model/parser-node";
import { ParserRequest } from "../../model/parser-request";
import { PageLoaderDataService } from "../../service/data/page-loader-data.service";
import { ParserOperationStatus } from "../../enum/parser-operation-status";
import { PaginationMode } from "../../enum/pagination-mode";
import { Pagination } from "../../model/pagination";
import { NodesListComponent } from "./nodes-list/nodes-list.component";
import { NodeStatus } from "../../enum/node-status";
import {current} from "codelyzer/util/syntaxKind";

@Component({
	selector: 'app-parser',
	templateUrl: './parser.component.html',
	styleUrls: ['./parser.component.scss']
})

export class ParserComponent implements OnInit {

	protected parserName: string = '';
	protected parserEnumData = ParserType.getData();

	protected cacheKeys = {
		parsedNodes: <string>'',
		currentNode: <string>''
	};

	protected parserSettings = this.config.parsers;

	public parserRequest: ParserRequest = new ParserRequest();

	public ParserOperationStatus = ParserOperationStatus;
	public NodeLevel = NodeLevel;
	public NodeStatus = NodeStatus;

	public highestLevel = true;
	public parserBreadcrumbs = [];

	protected scrollY = 0;

	/** Template variables **/
	public actionBeltClass: string = ''; // classes for action belt
	public actionBeltMaskClass: string = ''; // classes for action belt mask
	public scrollTopVisible: boolean = false; // is 'scroll top' button visible?
	public previousNodeAvailable: boolean = false; // is previous node button enabled/available?

	public nodesListComponent: NodesListComponent;

	constructor(
		private headerData: ContentHeaderDataService,
		private router: Router,
		private config: ConfigService,
		private parserService: ParserService,
		private pageLoaderDataService: PageLoaderDataService
	) { }

	ngOnInit() {
		this.run();

		this.router.events.forEach((event) => { // run on parser url address change;
			if (event instanceof NavigationEnd) {
				this.run();
			}
		});
	};

	ngOnDestroy() {
		window.removeEventListener('scroll', this.scrollEvent, true);
	}

	public scrollEvent = (event: any = null): void => {
		this.scrollY = window.scrollY;
		this.determineActionBeltClass();
	};

	/**
	 * Initialization function.
	 * Sets node data, names and sends initialize data request;
	 */
	public run() : void {
		window.addEventListener('scroll', this.scrollEvent, true);

		this.parserBreadcrumbs = [];

		this.scrollEvent();
		this.determineParserName();
		this.setHeaderData();

		this.initializeParserRequestObject();
		this.openCurrentNode();
	}

	/**
	 * Sets specified node as 'current' and opens it.
	 *
	 * @param node
	 * @param pagination
	 * @param scrollY
	 */
	public setAndOpenCurrentNode(node: ParserNode = null, pagination: Pagination = null, scrollY: number = 0) : void {
		this.parserRequest.currentNode = node;
		this.parserRequest.clearParsedData();

		if (node.nextLevel && this.parserRequest.level !== node.nextLevel) // change level if necessary
			this.parserRequest.level = node.nextLevel;
		else if (!node.nextLevel)
			this.parserRequest.level = node.level;

		if (pagination)
			this.parserRequest.pagination = pagination;

		if (scrollY)
			window.scroll(0, scrollY);

		this.openCurrentNode();
	}

	/**
	 * Open current node storages in parserRequest
	 *
	 * @param forceReload (boolean) - omnit cache and force reload data
	 */
	public openCurrentNode(forceReload: boolean = false): void {
		if (!this.parserRequest.currentNode || !this.parserRequest.currentNode.level) {
			this.parserRequest.currentNode = this.initializeParserNodeObject();
		}

		this.parserRequest.clearParsedData();

		if (forceReload) {
			this.parserRequest.ignoreCache = true;
		}

		this.sendParserRequest();
	}

	/**
	 * 	Checks and opens previous node;
	 *
	 * 	@return number - index of previous index;
	 */
	public openPreviousNode(): void {
		let currentBreadcrumbIndex = this.getCurrentBreadcrumbIndex();

		if (currentBreadcrumbIndex > 0) { // gets previous index from breadcrumbs and opens node
			this.setAndOpenCurrentNode(this.parserBreadcrumbs[(currentBreadcrumbIndex - 1)].node);
		}
	}

	/**
	 *
	 * 	@return number - index of previous index;
	 */
	private getCurrentBreadcrumbIndex(): number {
		if (this.parserBreadcrumbs.length > 1) {
			for (let x in this.parserBreadcrumbs) {
				let index = parseInt(x);

				if (this.parserBreadcrumbs[index].node === this.parserRequest.currentNode) {
					return index;
				}
			}
		}

		return -1;
	}

	/**
	 * Jumps to other node form the same level
	 *
	 * @param direction - next or previous
	 */
	public jumpBetweenNodes(direction: string) : void {

	}

	/**
	 * Changes page of currently viewed node.
	 *
	 * @param pagination - pagination settings;
	 */
	public changeNodePage(pagination: Pagination) {
		this.parserRequest.pagination = pagination;
		this.openCurrentNode();
	}

	/**
	 * Shows/hides modal with node settings;
	 */
	public toggleSettingsModal(): void {

	};

	public markCurrentNode(status: string): void {
		this.nodesListComponent = new NodesListComponent(this.parserService);
		this.nodesListComponent.parserRequest = this.parserRequest;
		this.nodesListComponent.markNode(this.parserRequest.currentNode, status);
		this.parserRequest = this.nodesListComponent.parserRequest;
	}

	public getCurrentNodeButtonClass(status: string): string {
		this.nodesListComponent = new NodesListComponent(this.parserService);

		return this.nodesListComponent.getNodeButtonClass(
			this.parserRequest.currentNode, status
		);
	}

	/**
	 * Determine action belt classes based on current scroll value;
	 */
	public determineActionBeltClass(): void {
		this.actionBeltClass = 'actionbelt_container' + ((this.scrollY > 80) ? ' fixed' : '');
		this.actionBeltMaskClass = 'actionbelt_mask' + ((this.scrollY > 80) ? ' visible' : '');
	}

	/**
	 * Creates ParserRequest model with initial settings;
	 */
	private initializeParserRequestObject() : void {
		this.parserRequest = new ParserRequest();
		this.parserRequest.parser = this.parserName;
		this.parserRequest.level = this.parserSettings[this.parserName+'_initial_level'];

		let initialParserPagination = this.parserSettings[this.parserName+'_initial_pagination'];

		if (initialParserPagination !== 'none') {
			this.parserRequest.pagination.active = true;
			this.parserRequest.pagination.mode = initialParserPagination;
		}
	}

	private initializeParserNodeObject() : ParserNode {
		let node = new ParserNode();

		node.parser = this.parserName;
		node.level = this.parserSettings[this.parserName+'_initial_level'];

		return node;
	}

	/**
	 * Generates cache keys for current parser + level + page/letter.
	 */
	private generateCacheKeys() : void {
		let keysSufix = '';

		keysSufix += (this.parserRequest.pagination.mode === PaginationMode.Numbers)
			? '.'+this.parserRequest.pagination.currentPage
			: '';
		keysSufix += (this.parserRequest.pagination.mode === PaginationMode.Letters)
			? '.'+this.parserRequest.pagination.currentLetter
			: '';

		this.cacheKeys.parsedNodes = 'nodes.'+this.parserName.toLowerCase()+'.'+this.parserRequest.level+keysSufix;
		this.cacheKeys.currentNode = 'node.'+this.parserName.toLowerCase()+'.'+this.parserRequest.level+keysSufix;
	}

	/**
	 * Sends data to parser API
	 */
	private sendParserRequest() {
		let currentBreadcrumbIndex = this.getCurrentBreadcrumbIndex();

		if (currentBreadcrumbIndex >= 0)  // save current scroll position;
			this.parserBreadcrumbs[currentBreadcrumbIndex].scrollY = this.scrollY;

		this.pageLoaderDataService.setProgress(1).show().enableRefreshingFromApi();

		this.parserService.executeAction(this.parserRequest).subscribe((response : ParserRequest) => {
			this.parserRequest = response;
			this.highestLevel = (this.parserRequest.currentNode.level === this.parserSettings[this.parserName+'_initial_level']);
			this.setBreadcrumb(response.currentNode, response.pagination);
			this.previousNodeAvailable = (this.getCurrentBreadcrumbIndex() > 0);
		}, () => {
			this.pageLoaderDataService.disableRefreshingFromApi().setProgress(100).hide();
		}, () => {
			this.pageLoaderDataService.disableRefreshingFromApi().setProgress(100).hide();
		});
	}

	private setBreadcrumb(node: ParserNode, pagination = null): void {
		let nodeIntLevel = NodeLevel.getNodeIntLevel(node);
		let previousBreadcrumbs = this.parserBreadcrumbs;
		let newBreadcrumbEntry = {
			node: node,
			scrollY: 0,
			pagination: (new Pagination(pagination)),
			final: true // final breadcrumb -> font-weight: bold;
		};

		if (nodeIntLevel < 1)
			return;
		else
			this.parserBreadcrumbs = [];

		if (previousBreadcrumbs.length > 0) {
			for (let prevBreadcrumbIndex in previousBreadcrumbs) {
				let oldBreadcrumbEntry = previousBreadcrumbs[prevBreadcrumbIndex];
				let oldBreadcrumbIntLevel = NodeLevel.getNodeIntLevel(oldBreadcrumbEntry.node);

				oldBreadcrumbEntry.final = false; // not final breadcrumb -> font-weight: normal;

				if (oldBreadcrumbIntLevel > nodeIntLevel) {
					this.parserBreadcrumbs.push(oldBreadcrumbEntry);
				}
			}
		}

		this.parserBreadcrumbs.push(newBreadcrumbEntry);
	}

	/**
	 * Determines parser name based by URL address;
	 */
	private determineParserName() : void {
		let urlArray = this.router.url.split('/');

		urlArray.forEach((value, index) => {
			if (value === 'parsers') {
				this.parserName = urlArray[(index+1)];
			}
		});
	}

	/**
	 * Sets data for content-header
	 *
	 * IMPORTANT: data musts set AFTER initialize parser board data
	 */
	private setHeaderData() : void {
		this.headerData.setElement('title1', 'Parser');
		this.headerData.setElement('title2', 'Scrapping images and movies');
		this.headerData.clearBreadcrumbs();
		this.headerData.addBreadcrumb({
			route: null,
			routeParams: {'parserName':this.parserName},
			label: 'Parsers',
			icon: 'fa-dashboard'
		});
		this.headerData.addBreadcrumb({
			route: 'app_parser',
			routeParams: {'parserName':this.parserName},
			label: (typeof this.parserEnumData[this.parserName] !== 'undefined')
				? this.parserEnumData[this.parserName]
				: null,
			icon: 'fa-dashboard'
		});
	}

}